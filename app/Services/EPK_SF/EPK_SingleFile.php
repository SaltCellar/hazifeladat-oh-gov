<?php
namespace App\Services\EPK_SF;

class EPK_SingleFile {

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Info */
    /* -------------------------------------------------------------------------------------------------------------- */

    /*
        // handle - PATTERN

        1   - A "valasztott-szak" Beküldésre került és helyes a formátum
        2   - A "erettsegi-eredmenyek" Beküldésre került és helyes a formátum
        3   - A "tobbletpontok" Beküldésre került és helyes a formátum (Optional | Variable)

        // handle - VALIDATION

        4   - A "valasztott-szak" Adatai érvényesek
        5   - A "erettsegi-eredmenyek" Adatai érvényesek
        6   - A "tobbletpontok" Adatai érvényesek (Optional | Variable)

        // scoreCalculation - CALCULATION

        1   - Kötelező tantárgyak ellenörzése (És minimum pont határ)
        1.a - Minimum ponthatár minden figyelembevett kötelező tantárgynál
        2   - A legjobban sikerült választható tantárgy
        3   - Minimum ponthatár a figyelembevett választható tantárgynál
        4   - Score Collection
        5   - Total & Moderates

    */

    /* -------------------------------------------------------------------------------------------------------------- */

    private static ? self $instance = null;

    public static function getInstance() : self {
        if (is_null(self::$instance))  {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        if (!is_null(self::$instance))  {
            throw new \RuntimeException('This class can be created only once!');
        }
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Config (Hard Kódolva) */
    /* -------------------------------------------------------------------------------------------------------------- */

    public const SUBJECT_ADVANCED_EXTRA_SCORE = 50;

    public const MINIMUM_PERCENT_LIMIT = 20;

    public const REQUIRED_SUBJECTS = [
        1 => false, // magyar nyelv és irodalom (Közép)
        2 => false, // történelem               (Közép)
        3 => false, // matematika               (Közép)
    ];

    /* -------------------------------------------------------------------------------------------------------------- */

    private function sendError(string $message) : array {
        return [
            'error'     => true,
            'message'   => 'hiba, '.$message,
        ];
    }

    private function sendSuccess(string $message) : array {
        return [
            'error'     => false,
            'message'   => $message,
        ];
    }

    private function sendDebug(mixed $data,bool $error = false) : array {
        return [
            'error'     => $error,
            'message'   => $data,
        ];
    }

    /* -------------------------------------------------------------------------------------------------------------- */

    public function handle(array $payload) : array {

        // Pattern Validation

        if ($r = $this->patternAttributeRequired($payload,['valasztott-szak'],['egyetem','kar','szak'])) {
            return $this->sendError($r);
        }

        if ($r = $this->patternArrayAttributeRequired($payload,['erettsegi-eredmenyek'],['nev','tipus','eredmeny'])) {
            return $this->sendError($r);
        }

        // Variálható többletpont validáció (Kategória alapján!)

        if (isset($payload['tobbletpontok'])) {
            foreach ($payload['tobbletpontok'] as $index => $extraScore) {
                switch ($extraScore['kategoria'] ?? null) {

                    case 'Nyelvvizsga' : {
                        if ($r = $this->patternAttributeRequired($payload,['tobbletpontok',$index],['tipus','nyelv'])) {
                            return $this->sendError($r);
                        }
                        break;
                    }

                    default : { return $this->sendError('a [tobbletpontok.'.$index.'.kategoria] mező kitöltése kötelező!'); }
                }
            }
        }

        // Data Validation

        $universityCourse = $this->getUniversityCourse($payload['valasztott-szak']);
        if (!$universityCourse) {
            return $this->sendError('a választott szak érvénytelen!');
        }

        $subjects = $this->getFormattedSubjectsData($payload['erettsegi-eredmenyek']);
        if ($subjects['error']) {
            return $this->sendError($subjects['error']);
        } else {
            $subjects = $subjects['result'];
        }

        $extraScores = $this->getFormattedExtraScores($payload['tobbletpontok'] ?? null);
        if ($extraScores['error']) {
            return $this->sendError($extraScores['error']);
        } else {
            $extraScores = $extraScores['result'];
        }

        // Score Calculation

        return $this->scoreCalculation($universityCourse,$subjects,$extraScores);
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Score Calculation */
    /* -------------------------------------------------------------------------------------------------------------- */

    private function scoreCalculation(array $universityCourse,array $subjects, array $extraScores) : array {

        // Kötelező tantárgyak ellenörzése (És minimum pont határ)

        $greatestRequiredSubjectIndex = null;

        $courseSubjects = $this->getRequiredAndChoosableSubjectAndAdvanced($universityCourse);

        foreach ($courseSubjects['required'] as $courseSubjectId => $courseSubjectIsAdvanced) {
            $pass = false;
            foreach ($subjects as $index => $subject) {
                if ($subject['id'] === $courseSubjectId && ( $subject['advanced'] || !$courseSubjectIsAdvanced )) {

                    // Minimum ponthatár minden figyelembevett kötelező tantárgynál

                    if ($subject['percent'] < self::MINIMUM_PERCENT_LIMIT) {
                        return $this->sendError('nem lehetséges a pontszámítás a ['.$subject['name'].'] tárgyból elért '.self::MINIMUM_PERCENT_LIMIT.'% alatti eredmény miatt!');
                    }

                    if ($universityCourse['subject'] === $subject['id']) {
                        $greatestRequiredSubjectIndex = $index;
                    }

                    $pass = true;

                }
            }
            if (!$pass) {
                return $this->sendError('nem lehetséges a pontszámítás a kötelező érettségi tárgyak hiánya miatt!');
            }
        }

        // A legjobban sikerült választható tantárgy

        $chossableSubject = $this->getMostGreatestChoosableSubject($subjects,$courseSubjects['choosable']);
        if (is_null($chossableSubject)) {
            return $this->sendError('nem lehetséges a pontszámítás a kötelezően válaszhtazó érettségi tárgy hiánya miatt!');
        }

        // Minimum ponthatár a figyelembevett választható tantárgynál

        if ($chossableSubject['percent'] < self::MINIMUM_PERCENT_LIMIT) {
            return $this->sendError('nem lehetséges a pontszámítás a ['.$chossableSubject['name'].'] tárgyból elért '.self::MINIMUM_PERCENT_LIMIT.'% alatti eredmény miatt!');
        }

        // Score

        $BASE_SCORE     = 0;
        $EXTRA_SCORE    = 0;

        $BASE_SCORE += $subjects[$greatestRequiredSubjectIndex]['percent'];
        $BASE_SCORE += $chossableSubject['percent'];

        if ($subjects[$greatestRequiredSubjectIndex]['advanced']) {
            $EXTRA_SCORE += self::SUBJECT_ADVANCED_EXTRA_SCORE;
        }

        if ($chossableSubject['advanced']) {
            $EXTRA_SCORE += self::SUBJECT_ADVANCED_EXTRA_SCORE;
        }

        foreach ($extraScores as $extraScore) {
            switch ($extraScore['category']) {

                case 'Nyelvvizsga' : {
                    $EXTRA_SCORE += $extraScore['score'];
                    break;
                }

            }
        }

        // Total & Moderates

        $BASE_SCORE = $BASE_SCORE * 2;

        if ($BASE_SCORE     > 400) { $BASE_SCORE   = 400; }
        if ($EXTRA_SCORE    > 100) { $EXTRA_SCORE  = 100; }

        $TOTAL_SCORE = $BASE_SCORE + $EXTRA_SCORE;

        return $this->sendSuccess($TOTAL_SCORE.' ('.$BASE_SCORE.' alappont + '.$EXTRA_SCORE.' többletpont)');
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Score Calculation Nest */
    /* -------------------------------------------------------------------------------------------------------------- */

    private function getRequiredAndChoosableSubjectAndAdvanced(array $universityCourse) : array {

        $result = [
            'required'  => self::REQUIRED_SUBJECTS,
            'choosable' => [],
        ];

        $curseSubjects = \App\Models\CourseSubject::query()
            ->where('university_course','=',$universityCourse['id'])
            ->getModels();

        foreach ( ( $curseSubjects ? : [] ) as $subject ) {
            $subject = $subject->toArray();
            $advanced = boolval($subject['advanced']);
            if ( $advanced || !isset($result['choosable'][$subject['subject']]) ) {
                $result['choosable'][$subject['subject']] = $advanced;
            }
        }

        $result['required'] [ $universityCourse[ 'subject' ] ] = boolval( $universityCourse[ 'subject_advanced' ] );

        return $result;

    }

    private function getMostGreatestChoosableSubject(array $subjects,array $choosableSubjectOfUniversityCourse) : ? array {
        $greatestIndex = null;
        foreach ($subjects as $index => $subject) {
            if (
                in_array($subject['id'],array_keys($choosableSubjectOfUniversityCourse)) &&
                ( $subject['advanced'] || !$choosableSubjectOfUniversityCourse[$subject['id']] )
            ) {
                if (is_null($greatestIndex) || $subjects[$greatestIndex]['percent'] < $subject['percent'] ) {
                    $greatestIndex = $index;
                }
            }
        }
        return $greatestIndex ? $subjects[$greatestIndex] : null;
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Pattern Validation Helpers */
    /* -------------------------------------------------------------------------------------------------------------- */

    private function patternArrayAttributeRequired(array $source,? array $depth, array $attributeNames) : ? string {
        foreach (($depth ? : []) as $i => $d) {
            $source = $source[$d] ?? null;
            if (!$source) return $this->patternErrorPrintAttributeRequired($d,array_slice($depth,0,$i));
        }

        foreach ($source as $sourceIndex => $sourceItem) {
            if ($r = $this->patternAttributeRequired($source,[$sourceIndex],$attributeNames,$depth)) {
                return $r;
            }
        }

        return null;
    }

    private function patternAttributeRequired(array $source,? array $depth,array $attributeNames,array $depthPass = null) : ? string {
        foreach (($depth ? : []) as $i => $d) {
            $source = $source[$d] ?? null;
            if (!$source) return $this->patternErrorPrintAttributeRequired($d,array_slice($depth,0,$i));
        }

        if ($depthPass) {
            $depth = array_merge($depthPass,($depth ? : []));
        }

        foreach ($attributeNames as $attributeName) {
            if (!isset($source[$attributeName]) || !$source[$attributeName]) {
                return $this->patternErrorPrintAttributeRequired($attributeName,$depth);
            } else if (!is_string($source[$attributeName])) {
                return $this->patternErrorPrintAttributeTypeInvalid($attributeName,'STRING',$depth);
            }
        }

        return null;
    }

    private function patternErrorPrintAttributeRequired(string $attributeName,array $depth = null) : string {
        return ( 'a ' . $this->patternAttributePrinter($attributeName,$depth) . ' mező kitöltése kötelező!' );
    }

    private function patternErrorPrintAttributeTypeInvalid(string $attributeName,string $type,array $depth = null) : string {
        return ( 'a ['.$attributeName.'] mező csak '.$type.' -típus lehet'.( $depth ? ( ' a ['.implode('/',$depth).'] -ban/ben' ) : '' ).'!' );
    }

    private function patternAttributePrinter(string $attributeName,array $depth = null) : string {
        return ( '[' . ( $depth ? ( implode('.',$depth) . '.' ) : '' ) . $attributeName . ']' );
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Data Validation Nest */
    /* -------------------------------------------------------------------------------------------------------------- */

    private function getUniversityCourse(array $data) : ? array {

        $universityCourse = \App\Models\UniversityCourse
            ::join('universities',  'universities.id',  '=', 'university_courses.university')
            ->join('faculties',     'faculties.id',     '=', 'university_courses.faculty')
            ->join('courses',       'courses.id',       '=', 'university_courses.course')
            ->select('university_courses.*')
            ->where('universities.name',    '=', $data['egyetem'])
            ->where('faculties.name',       '=', $data['kar'])
            ->where('courses.name',         '=', $data['szak'])
            ->first();

        return $universityCourse ? $universityCourse->getModel()->toArray() : null;

    }

    private function getFormattedSubjectsData(array $data) : array {
        $result = [];
        $error  = null;

        foreach ($data as $subjectIndex => $subject) {

            $subResult = [];

            if ($subjectModel = \App\Models\Subject::query()->where('name','=',$subject['nev'])->first() ) {
                $subjectModel = $subjectModel->getModel()->toArray();
                $subResult [ 'id' ]     = $subjectModel['id'];
                $subResult [ 'name' ]   = $subjectModel['name'];
            } else {
                return [
                    'result' => [],
                    'error' => 'a ['.$subject['nev'].'] nevü tantárgy érvénytelen!',
                ];
            }

            switch ($subject['tipus']) {
                case 'közép' : { $subResult [ 'advanced' ] = false; break; }
                case 'emelt' : { $subResult [ 'advanced' ] = true; break; }
                default : {
                    return [
                        'result' => [],
                        'error' => 'a ['.$subject['tipus'].'] típus érvénytelen a ['.$subject['nev'].'] tantárgynál!',
                    ];
                }
            }

            if (!!preg_match('/^[0-9]+%$/',$subject['eredmeny'])) {
                $subResult [ 'percent' ] = intval( ltrim($subject['eredmeny'],'%') );

                if ($subResult [ 'percent' ] > 100 || $subResult [ 'percent' ] < 0) {
                    return [
                        'result' => [],
                        'error' => 'a ['.$subject['eredmeny'].'] eredmény érvénytelen a ['.$subject['nev'].'] tantárgynál, minimum 0% maximum 100% lehet!',
                    ];
                }

            } else {
                return [
                    'result' => [],
                    'error' => 'a ['.$subject['eredmeny'].'] eredmény érvénytelen a ['.$subject['nev'].'] tantárgynál, minimum 0% maximum 100% lehet!',
                ];
            }

            // Duplication & Domination
            $skipInsert = false;
            foreach ($result as $index => $item) {
                if ($item['id'] === $subResult['id']) {

                    if ($item['advanced'] === $subResult['advanced']) {
                        return [
                            'result' => [],
                            'error' => 'a ['.$subResult['name'].'] tárgy többször szerepel ugyan azon a szinten!',
                        ];
                    }

                    if ($subResult['percent'] > $item['percent']) {
                        unset($result[$index]);
                    } else {
                        $skipInsert = true;
                    }

                }
            }

            if (!$skipInsert) {
                $result [] = $subResult;
            }
        }

        return [ 'result' => $result, 'error' => $error ];
    }

    private function getFormattedExtraScores(array $data = null) : array {
        $result = [];
        $error  = null;

        foreach ( ( $data ? : [] ) as $extraIndex => $extraItem) {

            $subResult  = [];
            $skipInsert = false;

            switch ($extraItem['kategoria']) {

                case 'Nyelvvizsga' : {

                    $subResult [ 'category' ] = 'Nyelvvizsga';

                    $languageLevelsRankAndScore = $this->getLanguageLevelsRankAndScore();

                    if ($languageModel = \App\Models\Language::query()->where('name','=',$extraItem['nyelv'])->first() ) {
                        $languageModel = $languageModel->getModel()->toArray();
                        $subResult [ 'id' ]         = $languageModel['id'];
                        $subResult [ 'language' ]   = $languageModel['name'];
                    } else {
                        return [
                            'result' => [],
                            'error' => 'a [Nyelvvizsga] - ['.$extraItem['nyelv'].'] érvénytelen nyelv a többletpontnál!',
                        ];
                    }

                    if (in_array($extraItem['tipus'],array_keys($languageLevelsRankAndScore))) {
                        $subResult [ 'level' ] = $extraItem['tipus'];
                        $subResult [ 'score' ] = $languageLevelsRankAndScore[ $extraItem['tipus'] ] [ 'score' ];
                    } else {
                        return [
                            'result' => [],
                            'error' => 'a [Nyelvvizsga] - ['.$extraItem['tipus'].'] érvénytelen típus a többletpontnál!',
                        ];
                    }

                    // Duplication & Domination
                    foreach ($result as $index => $item) {
                        if ($item['language'] === $subResult['language']) {
                            if ($item['level'] === $subResult['level']) {
                                return [
                                    'result' => [],
                                    'error' => 'a ['.$subResult['language'].'] nyelvvizsga többször szerepel ugyan azon a szinten!',
                                ];
                            } else if ($item['score'] >= $subResult['score']) {
                                $skipInsert = true;
                            } else {
                                unset($result[$index]);
                            }
                        }
                    }

                    break;
                }

                default : {
                    return [
                        'result' => [],
                        'error' => 'a ['.$extraItem['kategoria'].'] érvénytelen kategória a többletpontnál!',
                    ];
                }

            }

            if (!$skipInsert) {
                $result [] = $subResult;
            }

        }

        return [ 'result' => $result, 'error' => $error ];
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Data Validation Helpers */
    /* -------------------------------------------------------------------------------------------------------------- */

    private function getLanguageLevelsRankAndScore() : array {
        $models = \App\Models\LanguageScoreScale::query()->orderBy('value','asc')->getModels();
        $result = [];
        foreach ($models as $index => $model) {
            $model = $model->toArray();
            $result [$model['level']] = [
                'rank' => $index,
                'score' => $model['value'],
            ];
        }
        return $result;
    }

}
