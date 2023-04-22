<?php

namespace App\Services\EPK;

class HibaGyar {

    public static function attributeRequired(string $attributeName,array $parent = []) : string {
        return 'a ' . self::attributePrinter($attributeName,$parent) . ' mező kitöltése kötelező!';
    }

    public static function attributeStrictType(string $attributeName,string $type,array $parent = []) {
        return 'a ' . self::attributePrinter($attributeName,$parent) . ' mező csak ' . '['.$type.']' . ' típus lehet!';
    }

    public static function attributePrinter(string $attributeName,array $parent) : string {
        return '[' . ( $parent ? ( implode('.',$parent) . '.' ) : '' ) . $attributeName . ']';
    }

    /* -------------------------------------------------------------------------------------------------------------- */

    public const TYPE_INT       = 0;
    public const TYPE_STRING    = 1;

    public static function typeForceOrNull(mixed $value,int $type) : mixed {
        switch ($type) {
            case self::TYPE_INT     : return is_int($value)     ? $value : null;
            case self::TYPE_STRING  : return is_string($value)  ? $value : null;
            default : return null;
        }
    }

    /* -------------------------------------------------------------------------------------------------------------- */

    public static function primitiveValidator(array $data,array $validation,array $parent = []) : ? string {
        foreach ($validation as $attributeName => $attributeValidationRules) {
            if (isset($data[$attributeName])) {

                if (in_array('string',$attributeValidationRules) && !is_string($data[$attributeName])) {
                    return self::attributeStrictType($attributeName,'string',$parent);
                }

                if (in_array('integer',$attributeValidationRules) && !is_string($data[$attributeName])) {
                    return self::attributeStrictType($attributeName,'integer',$parent);
                }

                if (in_array('array',$attributeValidationRules) && !is_array($data[$attributeName])) {
                    return self::attributeStrictType($attributeName,'array',$parent);
                }

            } else {
                if (in_array('required',$attributeValidationRules)) {
                    return self::attributeRequired($attributeName,$parent);
                }
            }
        }

        return null;
    }

}
