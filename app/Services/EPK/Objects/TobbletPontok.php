<?php

namespace App\Services\EPK\Objects;

use App\Services\EPK\Extend\Ellenorizheto;
use App\Services\EPK\HibaGyar;
use App\Services\EPK\Implement\PontSzamito;
use App\Services\EPK\Objects\TobbletPont\Nyelvvizsga;
use \App\Services\EPK\Objects\TobbletPontTrait\Nyelvvizsga as NyelvvizsgaTrait;

class TobbletPontok extends Ellenorizheto implements PontSzamito {

    use NyelvvizsgaTrait;

    private const TOBBLET_PONT_KATEGORIA = [
        'Nyelvvizsga' => Nyelvvizsga::class,
    ];

    private array $tobbletPontok = [
        'Nyelvvizsga' => [],
    ];

    public function __construct(array $payloadPart,array $parent = []) {
        foreach ($payloadPart as $index => $tobbletpont) {
            if (is_array($tobbletpont)) {

                // Kategória mező kötelező

                $kategoria = $tobbletpont['kategoria'] ?? null;
                if (!$kategoria) {
                    $this->setError(HibaGyar::attributeRequired('kategoria',array_merge($parent,[$index])));
                    return;
                }

                // Kategória tipus valós

                if (!in_array($kategoria,array_keys(self::TOBBLET_PONT_KATEGORIA))) {
                    $this->setError('érvénytelen érték a ' .
                        HibaGyar::attributePrinter('kategoria',array_merge($parent,[$index])) .
                        'mezőben, csak [' . implode(array_keys(self::TOBBLET_PONT_KATEGORIA)) . '] lehet!'
                    );
                    return;
                }

                // Hozzáadás

                $tobbletPontClass = self::TOBBLET_PONT_KATEGORIA [ $kategoria ] ;
                $this->tobbletPontok[ $kategoria ] [] = new $tobbletPontClass ( $tobbletpont, array_merge($parent,[$index]) );

            } else {
                $this->setError(HibaGyar::attributeStrictType($index,'array',$parent));
                break;
            }
        }
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Ellenörzés */
    /* -------------------------------------------------------------------------------------------------------------- */

    protected function validate() : void {

        $this->validateNyelvvizsga();

        if ($this->isFailed()) return;

        $this->setValid();
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Ellenörzés Nyelvvizsga */
    /* -------------------------------------------------------------------------------------------------------------- */

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Műveletek */
    /* -------------------------------------------------------------------------------------------------------------- */

    /* -------------------------------------------------------------------------------------------------------------- */
    /* PontSzamito */
    /* -------------------------------------------------------------------------------------------------------------- */

    public function getAlapPont() : int {
        $this->validatedArea();
        $pont = 0;

        $pont += $this->pontSzamitoAlapNyelvvizsga();

        return $pont;
    }

    public function getTobbletPont() : int {
        $this->validatedArea();
        $pont = 0;

        $pont += $this->pontSzamitoTobbletNyelvvizsga();

        return $pont;
    }

}
