<?php

namespace MrBill\Domain;

use Exception;

class ExpenseRecord
{
    public $amount;
    public $hashtags = []; // items do not include "#"
    public $message = '';

    /**
     * Constructs a single ExpenseRecord from the message, ignoring newlines.
     * If your message may have multiple ExpenseRecord (one per line), do not use this.
     *
     * @param string $message
     * @throws Exception
     */
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
        $this->hashtags = array_values(array_unique($this->hashtags));

        $this->message = implode(' ', $parts);
    }

    public static function getAllExpensesFromMessage(string $message) : array
    {
        $result = [];
        $lines = explode("\n", $message);
        foreach ($lines as $line) {
            try {
                $result[] = new ExpenseRecord($line);
            } catch (Exception $e) {
            }
        }
        return $result;
    }

    public function getHashtagsCanonical() : string
    {
        return '#' . implode('#', $this->hashtags);
    }
}
