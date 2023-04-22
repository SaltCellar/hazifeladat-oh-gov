<?php

namespace App\Services\EPK;
use \App\Services\EPK\Objects\Jelentkezes;

class Pontszamitas {

    private Jelentkezes $jelentkezes;

    public function __construct(array $payload) {
        $this->jelentkezes = new Jelentkezes($payload);
    }

    /* -------------------------------------------------------------------------------------------------------------- */

    public const KOTELEZO_TARGYAK       = [
        1 => false, // magyar nyelv és irodalom (Közép)
        2 => false, // történelem               (Közép)
        3 => false, // matematika               (Közép)
    ];

    public const MINIMUM_PONT_HATAR     = 20;
    public const EMELT_TANTARGY_PONT    = 50;
    public const MAXIMUM_PONT_TOBBLET   = 100;
    public const MAXIMUM_PONT_ALAP      = 400;

    /* -------------------------------------------------------------------------------------------------------------- */

    public function szamitas() : array {

        if ($error = $this->jelentkezes->getError()) {
            return $this->sendError('hiba, '.$error);
        }

        $ALAP_PONT      = $this->jelentkezes->getAlapPont();
        $TOBBLET_PONT   = $this->jelentkezes->getTobbletPont();
        $OSSZES_PONT    = $ALAP_PONT + $TOBBLET_PONT;

        return $this->sendSuccess($OSSZES_PONT.' ('.$ALAP_PONT.' alappont + '.$TOBBLET_PONT.' többletpont)');
    }

    /* -------------------------------------------------------------------------------------------------------------- */

    private function sendError(string $message) : array {
        return [
            'response_code' => 400,
            'response_body' => $message,
        ];
    }
    private function sendSuccess(string $message) : array {
        return [
            'response_code' => 200,
            'response_body' => $message,
        ];
    }


}
