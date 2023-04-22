<?php

namespace App\Services\EPK\Objects;

use App\Services\EPK\Extend\Ellenorizheto;
use App\Services\EPK\HibaGyar;
use App\Services\EPK\Implement\PontSzamito;

use App\Services\EPK\Objects\Szak;

class Eredmenyek extends Ellenorizheto implements PontSzamito {

    private array $eredmenyek = [];

    private ? Eredmeny $kotelezoTantargy        = null;
    private ? Eredmeny $kotelezoenValasztott    = null;

    private Szak $szak;

    public function __construct(Szak $szak,array $payloadPart,array $parent = []) {

        foreach ($payloadPart as $index => $eredmeny) {
            if (is_array($eredmeny)) {
                $this->eredmenyek [] = new Eredmeny($eredmeny,array_merge($parent,[$index]));
            } else {
                $this->setError(HibaGyar::attributeStrictType($index,'array',$parent));
                break;
            }
        }

        if ($this->isFailed()) return;

        $this->szak = $szak;

    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Ellenörzés */
    /* -------------------------------------------------------------------------------------------------------------- */

    protected function validate(): void {

        // Érvényesség

        foreach ($this->eredmenyek as $eredmeny) {
            $this->setError($eredmeny->getError());
            if ($this->isFailed()) return;
        }

        // Duplázódás

        $eredmenyVizsgalat = [];
        foreach ($this->eredmenyek as $eredmeny) {
            $code = $eredmeny->getSubjectId() . '#' . ( $eredmeny->isEmeltSzintu() ? 'E' : 'K' );
            if (in_array($code,$eredmenyVizsgalat)) {
                $this->setError('a ['.$eredmeny->getNev().'] tárgy többször szerepel ugyan azon a szinten!');
                return;
            } else {
                $eredmenyVizsgalat [] = $code;
            }
        }

        if ($this->isFailed()) return;

        $eredmenyek                             = $this->getDominaltEredmenyek();
        $formazottEredmenyek                    = $this->formatEredmenyekAzonositoEsSzint($eredmenyek);

        $kotelezoTantargyakAzonositoEsSzint     = $this->szak->getKotelezoTantargyakAzonositoEsSzint();
        $valaszthatoTantargyakAzonositoEsSzint  = $this->szak->getValaszthatoTantargyakAzonositoEsSzint();

        $kotelezoTargyAzonosito                 = array_keys($this->szak->getKotelezoTantargyakAzonositoEsSzint(false))[0];

        // Szerepelnek a kötelező tárgyak

        foreach ($kotelezoTantargyakAzonositoEsSzint as $targyAzonosito => $targyEmelt) {
            if (
                !in_array($targyAzonosito,array_keys($formazottEredmenyek)) ||
                ( $targyEmelt && !$formazottEredmenyek[$targyAzonosito] )
            ) {
                $this->setError('nem lehetséges a pontszámítás a kötelező érettségi tárgyak hiánya miatt!');
                break;
            }
        }

        if ($this->isFailed()) return;

        foreach ($eredmenyek as $eredmeny) {
            if ($eredmeny->getSubjectId() === $kotelezoTargyAzonosito) {
                $this->kotelezoTantargy = $eredmeny;
            }
        }

        if ($this->isFailed()) return;

        // Szerepel egy kötelezően választott tárgy

        $this->kotelezoenValasztott = null;
        foreach ($eredmenyek as $eredmeny) {
            if (
                in_array($eredmeny->getSubjectId(),array_keys($valaszthatoTantargyakAzonositoEsSzint)) &&
                ( !$valaszthatoTantargyakAzonositoEsSzint[$eredmeny->getSubjectId()] || $eredmeny->isEmeltSzintu() ) &&
                ( is_null($this->kotelezoenValasztott) || $this->kotelezoenValasztott->getSzazalek() < $eredmeny->getSzazalek() )
            ) {
                $this->kotelezoenValasztott = $eredmeny;
            }
        }
        if (is_null($this->kotelezoenValasztott)) {
            $this->setError('nem lehetséges a pontszámítás a kötelezően válaszhtazó érettségi tárgy hiánya miatt!');
        }

        if ($this->isFailed()) return;

        // A kötelező és a legjobban sikerült kötelezően választott tárgybol elérte a minimumot

        foreach ($eredmenyek as $eredmeny) {
            if (!in_array($eredmeny->getSubjectId(),array_keys($kotelezoTantargyakAzonositoEsSzint))) continue;

            if ($eredmeny->getSzazalek() < \App\Services\EPK\Pontszamitas::MINIMUM_PONT_HATAR) {
                $this->setError('nem lehetséges a pontszámítás a ['.$eredmeny->getNev().'] tárgyból elért '.\App\Services\EPK\Pontszamitas::MINIMUM_PONT_HATAR.'% alatti eredmény miatt!');
                break;
            }
        }

        if ($this->isFailed()) return;

        if ($this->kotelezoenValasztott->getSzazalek() < \App\Services\EPK\Pontszamitas::MINIMUM_PONT_HATAR) {
            $this->setError('nem lehetséges a pontszámítás a ['.$this->kotelezoenValasztott->getNev().'] tárgyból elért '.\App\Services\EPK\Pontszamitas::MINIMUM_PONT_HATAR.'% alatti eredmény miatt!');
        }

        if ($this->isFailed()) return;

        // Érvényesítés

        $this->setValid();

    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* Műveletek */
    /* -------------------------------------------------------------------------------------------------------------- */

    private function getDominaltEredmenyek() : array {
        $result = [];
        foreach ($this->eredmenyek as $eredmeny) {
            if (
                !isset($result[ $eredmeny->getNev() ]) ||
                $result[$eredmeny->getNev()]->getSzazalek() < $eredmeny->getSzazalek()
            ) {
                $result [ $eredmeny->getNev() ] = $eredmeny;
            }
        }
        return $result;
    }

    private function formatEredmenyekAzonositoEsSzint(array $eredmenyek) : array {
        $result = [];
        foreach ($eredmenyek as $eredmeny) {
            $result [ $eredmeny->getSubjectId() ] = $eredmeny->isEmeltSzintu();
        }
        return $result;
    }

    /* -------------------------------------------------------------------------------------------------------------- */
    /* PontSzamito */
    /* -------------------------------------------------------------------------------------------------------------- */

    public function getAlapPont() : int {
        $this->validatedArea();

        $pont = 0;

        $pont += $this->kotelezoTantargy->getSzazalek();
        $pont += $this->kotelezoenValasztott->getSzazalek();
        $pont *= 2;

        return $pont;
    }

    public function getTobbletPont() : int {
        $this->validatedArea();

        $pont = 0;

        if ($this->kotelezoTantargy->isEmeltSzintu()) {
            $pont += \App\Services\EPK\Pontszamitas::EMELT_TANTARGY_PONT;
        }
        if ($this->kotelezoenValasztott->isEmeltSzintu()) {
            $pont += \App\Services\EPK\Pontszamitas::EMELT_TANTARGY_PONT;
        }

        return $pont;
    }

}
