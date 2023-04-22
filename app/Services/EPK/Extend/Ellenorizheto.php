<?php

namespace App\Services\EPK\Extend;

class Ellenorizheto {

    private ? bool      $ellenorizheto_valid = null;
    private ? string    $ellenorizheto_error = null;

    /* -------------------------------------------------------------------------------------------------------------- */

    private function isValidated() : bool {
        return $this->ellenorizheto_valid !== null;
    }

    protected final function isValid() : bool {
        return $this->ellenorizheto_valid === true;
    }

    protected final function isFailed() : bool {
        return $this->ellenorizheto_valid === false;
    }

    /* -------------------------------------------------------------------------------------------------------------- */

    protected final function setValid() : void {
        if (!$this->isValidated()) {
            $this->ellenorizheto_valid = true;
        }
    }

    protected final function setError(? string $error) : void {
        if (!is_null($error) && !$this->isValidated()) {
            $this->ellenorizheto_valid = false;
            $this->ellenorizheto_error = $error;
        }
    }

    /* -------------------------------------------------------------------------------------------------------------- */

    protected final function validatedArea() : void {
        if (!$this->isValid()) {
            throw new \RuntimeException('a ['.get_called_class().'] osztályon olyan fügvény hívás történt, ami előt sikeres validáció szükséges!');
        }
    }

    /* -------------------------------------------------------------------------------------------------------------- */

    public final function getError() : ? string {
        if (!$this->isValidated()) {
            $this->validate();
            if (!$this->isValidated()) {
                throw new \RuntimeException('a ['.get_called_class().'] osztályon nem Érvényes és nem is Érvénytelen, [(fn)setValid] használata elmaradt!');
            }
        }
        return $this->ellenorizheto_error;
    }

    /* -------------------------------------------------------------------------------------------------------------- */

    protected function validate() : void {
        $this->setError('[(fn)validate] nincs beépítve a [' . get_called_class() .'] osztályba!' );
    }

}
