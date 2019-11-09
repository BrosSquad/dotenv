<?php


namespace BrosSquad\DotEnv;


class TypeChecker implements ValueType
{
    private $emptyStringAsNull;

    public function __construct(bool $emptyStringAsNull)
    {
        $this->emptyStringAsNull = $emptyStringAsNull;
    }

    public function detectValue(string $value)
    {
        $toLower = strtolower($value);

        // Detecting NULL
        if (strcmp('null', $toLower) === 0 || ($this->emptyStringAsNull === true && strcmp('', $toLower) === 0)) {
            return null;
        }

        // Detecting BOOLEANS
        if (($isBoolean = $this->checkForBoolean($toLower)) !== null) {
            return (bool)$isBoolean;
        }

        // Detecting FLOATS
        $trimmed = ltrim($toLower, '0');



        $casted = (float)$toLower;

        if (($casted !== 0.0 || strcmp('.0', $trimmed) === 0) && preg_match('/^\d+\.\d+$/', $toLower) === 1) {
            return $casted;
        }

        // Detecting INTEGERS
        $casted = (int)$trimmed;

        if ($casted !== 0 || strcmp('', $trimmed) === 0) {
            return $casted;
        }

        return $value;
    }


    private function checkForBoolean(string $value): ?int
    {
        switch ($value) {
            case 'ok':
//            case '1':
            case 'true':
            case 'yes':
            case 'y':
                return true;
            case 'no':
//            case '0':
            case 'false':
                return false;
            default:
                   return null;
        }
    }
}