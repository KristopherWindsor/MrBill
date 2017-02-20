<?php

namespace MrBill\Domain;

use Exception;

class ExpenseRecord
{
    public $amount;
    public $hashtags = []; // items do not include "#"
    public $message = '';

    public function __construct(string $message)
    {
        $parts = array_filter(explode(' ', str_replace("\n", ' ', $message)), 'trim');
        if (count($parts) < 2)
            throw new Exception();

        $firstPart = array_shift($parts);
        $amount = str_replace(['$', '¢', '£', '€'], '', $firstPart);
        if (!ctype_digit(str_replace('.', '', $amount, $countOutput)) || $countOutput > 1)
            throw new Exception();
        $this->amount = (float) $amount;

        foreach ($parts as $part) {
            if (substr($part, 0, 1) == '#') {
                $this->hashtags[] = substr($part, 1);
            }
        }
        if (!$this->hashtags)
            throw new Exception();

        $this->message = implode(' ', $parts);
    }

    public static function getExpenseRecordIfValid(string $message) : ?ExpenseRecord
    {
        try {
            return new self($message);
        } catch (Exception $e) {
            return null;
        }
    }
}
