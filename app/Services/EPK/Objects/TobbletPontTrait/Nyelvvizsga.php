<?php

namespace App\Services\EPK\Objects\TobbletPontTrait;

trait Nyelvvizsga {

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Ellenörzés */
    /* -------------------------------------------------------------------------------------------------------------- */

    private function validateNyelvvizsga() : void {

        // Érvényesség

        foreach ( $this->tobbletPontok['Nyelvvizsga'] as $nyelvvizsga ) {
            $this->setError($nyelvvizsga->getError());
            if ($this->isFailed()) return;
        }

        // Duplázódás

        $nyelvVizsgalat = [];
        foreach ( $this->tobbletPontok['Nyelvvizsga'] as $nyelvvizsga ) {

            $code = $nyelvvizsga->getNyelv() . '#' . $nyelvvizsga->getTipus();
            if (in_array($code,$nyelvVizsgalat)) {
                $this->setError('a ['.$nyelvvizsga->getNyelv().'] nyelvvizsga többször szerepel ugyan azon a szinten!');
                return;
            } else {
                $nyelvVizsgalat [] = $code;
            }

        }


    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Műveletek */
    /* -------------------------------------------------------------------------------------------------------------- */

    private function getDominaltNyelvvizsgak() : array {
        $result = [];
        foreach ($this->tobbletPontok['Nyelvvizsga'] as $nyelvvizsga) {
            if (
                !isset($result[ $nyelvvizsga->getNyelv() ]) ||
                ($result[ $nyelvvizsga->getNyelv() ])->getRank() < $nyelvvizsga->getRank()
            ) {
                $result [ $nyelvvizsga->getNyelv() ] = $nyelvvizsga;
            }
        }
        return $result;
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* PontSzamito */
    /* -------------------------------------------------------------------------------------------------------------- */

    private function pontSzamitoAlapNyelvvizsga() : int {
        return 0;
    }

    private function pontSzamitoTobbletNyelvvizsga() : int {
        $pont = 0;

        foreach ($this->getDominaltNyelvvizsgak() as $nyelvvizsga) {
            $pont += $nyelvvizsga->getScore();
        }

        return $pont;
    }


}
