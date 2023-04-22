<?php

namespace App\Services\EPK\Objects;
use App\Models\Subject;
use App\Services\EPK\Extend\Ellenorizheto;
use App\Services\EPK\HibaGyar;

class Eredmeny extends Ellenorizheto {

    private ? string $nev           = null;
    private ? string $tipus         = null;
    private ? string $eredmeny      = null;

    private Subject $model;

    private bool    $advanced;
    private int     $percent;


    public function __construct(array $payloadPart,array $parent = []) {

        $this->setError(HibaGyar::primitiveValidator($payloadPart,[
            'nev'       => [ 'required', 'string' ],
            'tipus'     => [ 'required', 'string' ],
            'eredmeny'  => [ 'required', 'string' ],
        ],$parent));

        $this->nev      = $payloadPart['nev']       ?? null;
        $this->tipus    = $payloadPart['tipus']     ?? null;
        $this->eredmeny = $payloadPart['eredmeny']  ?? null;

    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Ellenörzés */
    /* -------------------------------------------------------------------------------------------------------------- */

    protected function validate(): void {

        // Név ( és Id )

        if ($subjectModel = \App\Models\Subject::query()->where('name','=',$this->nev)->first() ) {
            $this->model = $subjectModel->getModel();
        } else {
            $this->setError('a ['.$this->nev.'] nevü tantárgy érvénytelen!');
        }

        if ($this->isFailed()) return;

        // Emeltszintü

        if (in_array($this->tipus,['közép','emelt'])) {
            $this->advanced = $this->tipus === 'emelt';
        } else {
            $this->setError('a ['.$this->tipus.'] típus érvénytelen a ['.$this->nev.'] tantárgynál!');
        }

        if ($this->isFailed()) return;

        // Eredmény

        if (!!preg_match('/^[0-9]+%$/',$this->eredmeny)) {
            $this->percent = intval( ltrim($this->eredmeny,'%') );
            if ($this->percent > 100 || $this->percent < 0) {
                $this->setError('a ['.$this->eredmeny.'] eredmény érvénytelen a ['.$this->nev.'] tantárgynál, minimum 0% maximum 100% lehet!');
            }
        } else {
            $this->setError('a ['.$this->eredmeny.'] eredmény érvénytelen a ['.$this->nev.'] tantárgynál, minimum 0% maximum 100% lehet!');
        }

        // Érvényesítés

        $this->setValid();

    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Műveletek */
    /* -------------------------------------------------------------------------------------------------------------- */

    public function getNev() : string {
        $this->validatedArea();

        return $this->nev;
    }

    public function isEmeltSzintu() : bool {
        $this->validatedArea();

        return $this->advanced;
    }

    public function getSzazalek() : int {
        $this->validatedArea();

        return $this->percent;
    }

    public function getSubjectId() : int {
        $this->validatedArea();

        return $this->model->id;
    }

}
