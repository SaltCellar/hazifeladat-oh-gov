<?php

namespace App\Services\EPK\Objects;

use App\Services\EPK\Extend\Ellenorizheto;
use App\Services\EPK\HibaGyar;

class Szak extends Ellenorizheto {

    private ? string $egyetem   = null;
    private ? string $kar       = null;
    private ? string $szak      = null;

    private ? \App\Models\UniversityCourse $model = null;

    public function __construct(array $payloadPart,array $parent = []) {

        $this->setError(HibaGyar::primitiveValidator($payloadPart,[
            'egyetem'   => [ 'required', 'string' ],
            'kar'       => [ 'required', 'string' ],
            'szak'      => [ 'required', 'string' ],
        ],$parent));

        if ($this->isFailed()) return;

        $this->egyetem  = $payloadPart['egyetem'] ?? null;
        $this->kar      = $payloadPart['kar']     ?? null;
        $this->szak     = $payloadPart['szak']    ?? null;

    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Ellenörzés */
    /* -------------------------------------------------------------------------------------------------------------- */

    protected function validate() : void {

        $this->model = \App\Models\UniversityCourse
            ::join('universities',  'universities.id',  '=', 'university_courses.university')
            ->join('faculties',     'faculties.id',     '=', 'university_courses.faculty')
            ->join('courses',       'courses.id',       '=', 'university_courses.course')
            ->select('university_courses.*')
            ->where('universities.name',    '=', $this->egyetem)
            ->where('faculties.name',       '=', $this->kar)
            ->where('courses.name',         '=', $this->szak)
            ->first();

        if ($this->model) {
            $this->setValid();
        } else {
            $this->setError('a választott szak érvénytelen!');
        }

    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Műveletek */
    /* -------------------------------------------------------------------------------------------------------------- */

    public function getKotelezoTantargyakAzonositoEsSzint(bool $alapKovetelmennyel = true) : array {
        $this->validatedArea();

        if ($alapKovetelmennyel) {

            // A tárgyhoz kötelező + Az alapkövetelmény

            $result = \App\Services\EPK\Pontszamitas::KOTELEZO_TARGYAK;
            $result [ $this->model->subject ] = $this->model->subject_advanced;

            return $result;

        } else {

            // Csak ami a tárgyhoz kötelező

            return  [ $this->model->subject => boolval($this->model->subject_advanced) ];

        }

    }

    public function getValaszthatoTantargyakAzonositoEsSzint() : array {
        $this->validatedArea();

        $result = [];

        $universitySubjects = \App\Models\CourseSubject::query()
            ->where('university_course','=',$this->model->id)
            ->getModels();

        foreach ($universitySubjects as $universitySubject) {
            $result [ $universitySubject->subject ] = boolval($universitySubject->advanced);
        }

        return $result;
    }

}
