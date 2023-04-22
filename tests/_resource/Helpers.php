<?php

namespace Tests\_resource;

trait Helpers {

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Helpers */
    /* -------------------------------------------------------------------------------------------------------------- */

    private function getInvalidString() : string {
        return '_TESZT_HELYTELEN_ÉRTEK_';
    }

    /* -------------------------------------------------------------------------------------------------------------- */

    private function getValidPattern() : array {
        return [
            'valasztott-szak' => [
                'egyetem'       => 'ELTE',
                'kar'           => 'IK',
                'szak'          => 'Programtervező informatikus',
            ],
            'erettsegi-eredmenyek' => [
                [
                    'nev'       => 'magyar nyelv és irodalom',
                    'tipus'     => 'közép',
                    'eredmeny'  => '75%',
                ],
                [
                    'nev'       => 'történelem',
                    'tipus'     => 'közép',
                    'eredmeny'  => '75%',
                ],
                [
                    'nev'       => 'matematika',
                    'tipus'     => 'emelt',
                    'eredmeny'  => '75%',
                ],

                [
                    'nev'       => 'angol nyelv',
                    'tipus'     => 'közép',
                    'eredmeny'  => '75%',
                ],
                [
                    'nev'       => 'informatika',
                    'tipus'     => 'közép',
                    'eredmeny'  => '75%',
                ],
                [
                    'nev'       => 'fizika',
                    'tipus'     => 'közép',
                    'eredmeny'  => '75%',
                ],
            ],
            'tobbletpontok' => [
                [
                    'kategoria' => 'Nyelvvizsga',
                    'tipus'     => 'B2',
                    'nyelv'     => 'angol',
                ],
            ],
        ];
    }

    private function getValidPatternMaximum() : array {
        return [
            'valasztott-szak' => [
                'egyetem'       => 'ELTE',
                'kar'           => 'IK',
                'szak'          => 'Programtervező informatikus',
            ],
            'erettsegi-eredmenyek' => [

                // Kotelezo (ELTE IK PI)
                [
                    'nev'       => 'magyar nyelv és irodalom',
                    'tipus'     => 'emelt',
                    'eredmeny'  => '100%',
                ],
                [
                    'nev'       => 'történelem',
                    'tipus'     => 'emelt',
                    'eredmeny'  => '100%',
                ],
                [
                    'nev'       => 'matematika',
                    'tipus'     => 'emelt',
                    'eredmeny'  => '100%',
                ],

                // Valasztott (ELTE IK PI)
                [
                    'nev'       => 'informatika',
                    'tipus'     => 'emelt',
                    'eredmeny'  => '100%',
                ],

            ],
            'tobbletpontok' => [
                [
                    'kategoria' => 'Nyelvvizsga',
                    'tipus'     => 'C1',
                    'nyelv'     => 'angol',
                ],
                [
                    'kategoria' => 'Nyelvvizsga',
                    'tipus'     => 'C1',
                    'nyelv'     => 'német',
                ],
            ],
        ];
    }

    private function getValidPatternMinimum() : array {
        return [
            'valasztott-szak' => [
                'egyetem'       => 'ELTE',
                'kar'           => 'IK',
                'szak'          => 'Programtervező informatikus',
            ],
            'erettsegi-eredmenyek' => [

                // Kotelezo (ELTE IK PI)
                [
                    'nev'       => 'magyar nyelv és irodalom',
                    'tipus'     => 'közép',
                    'eredmeny'  => '21%',
                ],
                [
                    'nev'       => 'történelem',
                    'tipus'     => 'közép',
                    'eredmeny'  => '21%',
                ],
                [
                    'nev'       => 'matematika',
                    'tipus'     => 'közép',
                    'eredmeny'  => '21%',
                ],

                // Valasztott (ELTE IK PI)
                [
                    'nev'       => 'informatika',
                    'tipus'     => 'közép',
                    'eredmeny'  => '21%',
                ],

            ],
        ];
    }

    /* -------------------------------------------------------------------------------------------------------------- */



}
