<?php

namespace App\Services\EPK\Extend;

class TobbletPont extends Ellenorizheto {

    protected function getTobbletPontKategory() : string {
        $classParts = explode(DIRECTORY_SEPARATOR,get_called_class());
        return end($classParts);
    }

}
