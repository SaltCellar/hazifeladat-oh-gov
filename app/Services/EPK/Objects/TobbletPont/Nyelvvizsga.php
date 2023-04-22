<?php

namespace App\Services\EPK\Objects\TobbletPont;

use App\Models\Language;
use App\Services\EPK\Extend\TobbletPont;
use App\Services\EPK\HibaGyar;
use App\Services\EPK\Implement\PontSzamito;

class Nyelvvizsga extends TobbletPont {

    private Language $model;
    private ? string $nyelv;
    private ? string $tipus;

    private int $score;
    private int $rank;

    public function __construct(array $payloadPart,array $parent = []) {

        $this->setError(HibaGyar::primitiveValidator($payloadPart,[
            'tipus'     => [ 'required', 'string' ],
            'nyelv'     => [ 'required', 'string' ],
        ],$parent));

        $this->nyelv     = $payloadPart['nyelv'] ?? null;
        $this->tipus     = $payloadPart['tipus'] ?? null;

    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Ellenörzés */
    /* -------------------------------------------------------------------------------------------------------------- */

    protected function validate() : void {

        // Nyelv

        if ($model = \App\Models\Language::query()->where('name','=',$this->nyelv)->first()) {
            $this->model = $model->getModel();
        } else {
            $this->setError('a ['.$this->getTobbletPontKategory().'] - ['.$this->nyelv.'] érvénytelen nyelv a többletpontnál!');
        }

        if ($this->isFailed()) return;

        // Típus

        $languageLevelsRankAndScore = $this->getLanguageLevelsRankAndScore();

        if (in_array($this->tipus,array_keys($languageLevelsRankAndScore))) {
            $this->rank     = $languageLevelsRankAndScore[ $this->tipus ] [ 'rank' ];
            $this->score    = $languageLevelsRankAndScore[ $this->tipus ] [ 'score' ];
        } else {
            $this->setError('a ['.$this->getTobbletPontKategory().'] - ['.$this->tipus.'] érvénytelen típus a többletpontnál!');
        }

        // Érvényesítés

        $this->setValid();

    }

    /* -------------------------------------------------------------------------------------------------------------- */

    private ? array $cache_languageLevelsRankAndScore = null;

    private function getLanguageLevelsRankAndScore() : array {
        if (is_null($this->cache_languageLevelsRankAndScore)) {
            $models = \App\Models\LanguageScoreScale::query()->orderBy('value','asc')->getModels();
            $result = [];
            foreach ($models as $index => $model) {
                $result [$model->level] = [
                    'rank' => $index,
                    'score' => $model->value,
                ];
            }
            $this->cache_languageLevelsRankAndScore = $result;
        }

        return $this->cache_languageLevelsRankAndScore;
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Műveletek */
    /* -------------------------------------------------------------------------------------------------------------- */

    public function getNyelv() : string {
        $this->validatedArea();

        return $this->nyelv;
    }

    public function getTipus() : string {
        $this->validatedArea();

        return $this->tipus;
    }

    public function getRank() : int {
        $this->validatedArea();

        return $this->rank;
    }

    public function getScore() : int {
        $this->validatedArea();

        return $this->score;
    }

}
