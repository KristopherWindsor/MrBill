<?php

namespace MrBill\Domain;

use MrBill\Model\Expense;
use MrBill\Model\Message;

class ExpensesFromMessageParser
{
    public function parse(Message $message) : array
    {
        $result = [];

        if ($message->isFromUser) {
            $lines = explode("\n", $message->message);

            foreach ($lines as $line) {
                if ($data = $this->parseSingleLine($line)) {
                    $result[] = Expense::createFromMessage(
                        $message->accountId,
                        $message->timestamp,
                        $data['amount'] * ExpenseSet::CENTS_PER_DOLLAR,
                        $data['hashtags'],
                        $data['description'],
                        ['message' => $message->toMap()]
                    );
                }
            }
        }

        return $result;
    }

    protected function parseSingleLine(string $text) : array
    {
        assert(strpos($text, "\n") === false);

        $parts = array_filter(explode(' ', $text), 'trim');
        if (count($parts) < 2)
            return [];

        $returnData = [];

        $firstPart = array_shift($parts);
        $amount = str_replace(['$', '¢', '£', '€'], '', $firstPart);
        if (!ctype_digit(str_replace('.', '', $amount, $countOutput)) || $countOutput > 1)
            return [];
        $returnData['amount'] = (float) $amount;

        $returnData['hashtags'] = $this->extractHashtagsFromParts($parts);
        if (!$returnData['hashtags'])
            return [];

        $returnData['description'] = implode(' ', $parts);

        return $returnData;
    }

    protected function extractHashtagsFromParts(array $parts) : array
    {
        $hashtags = [];
        foreach ($parts as $part) {
            if (substr($part, 0, 1) == '#') {
                $hashtags[] = substr($part, 1);
            }
        }

        $hashtags = array_values(array_unique($hashtags));
        sort($hashtags);

        return $hashtags;
    }
}
