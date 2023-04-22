<?php

namespace App\Services\EPK\Objects;

use App\Services\EPK\Extend\Ellenorizheto;
use App\Services\EPK\HibaGyar;
use App\Services\EPK\Implement\PontSzamito;

class Jelentkezes extends Ellenorizheto implements PontSzamito {

    private Szak            $szak;
    private Eredmenyek      $eredmenyek;
    private TobbletPontok   $tobbletPontok;

    public function __construct(array $payload) {

        $this->setError(HibaGyar::primitiveValidator($payload,[
            'valasztott-szak'       => [ 'required', 'arrra' ],
            'erettsegi-eredmenyek'  => [ 'required', 'arrra' ],
            'tobbletpontok'         => [ 'array' ],
        ]));

        if ($this->isFailed()) return;

        $this->szak             = new Szak($payload['valasztott-szak'] ?? [],['valasztott-szak']);

        $this->eredmenyek       = new Eredmenyek($this->szak,$payload['erettsegi-eredmenyek'] ?? [],['erettsegi-eredmenyek']);

        $this->tobbletPontok    = new TobbletPontok($payload['tobbletpontok'] ?? [],['tobbletpontok']);

    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Eredmények */
    /* -------------------------------------------------------------------------------------------------------------- */

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Ellenörzés */
    /* -------------------------------------------------------------------------------------------------------------- */

    protected function validate() : void {

        $this->setError($this->szak->getError());

        if ($this->isFailed()) return;

        $this->setError($this->eredmenyek->getError());

        if ($this->isFailed()) return;

        $this->setError($this->tobbletPontok->getError());

        if ($this->isFailed()) return;

        $this->setValid();
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Műveletek */
    /* -------------------------------------------------------------------------------------------------------------- */

    public function getAlapPont() : int {
        $this->validatedArea();

        $pont = 0;
        $pont += $this->eredmenyek->getAlapPont();
        $pont += $this->tobbletPontok->getAlapPont();

        return ( $pont > \App\Services\EPK\Pontszamitas::MAXIMUM_PONT_ALAP ) ? \App\Services\EPK\Pontszamitas::MAXIMUM_PONT_ALAP : $pont;
    }

    public function getTobbletPont() : int {
        $this->validatedArea();

        $pont = 0;
        $pont += $this->eredmenyek->getTobbletPont();
        $pont += $this->tobbletPontok->getTobbletPont();

        return ( $pont > \App\Services\EPK\Pontszamitas::MAXIMUM_PONT_TOBBLET ) ? \App\Services\EPK\Pontszamitas::MAXIMUM_PONT_TOBBLET : $pont;
    }

}
