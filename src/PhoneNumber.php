<?php

namespace MrBill;

use Exception;
use JsonSerializable;

class PhoneNumber implements JsonSerializable
{
    public $scalar;

    public function __construct(string $phoneNumber)
    {
        $this->scalar = str_replace(['+', ' ', '(', ')', '-'], '', $phoneNumber);
        if (!ctype_digit($this->scalar))
            throw new Exception();
    }

    public function __toString() : string
    {
        return $this->scalar;
    }

    public function jsonSerialize() : int
    {
        return (int) $this->scalar;
    }

    public static function getIfValid(string $phoneNumber) : ?PhoneNumber
    {
        try {
            return new PhoneNumber($phoneNumber);
        } catch (Exception $e) {
            return null;
        }
    }
}
