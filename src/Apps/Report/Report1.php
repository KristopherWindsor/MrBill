<?php

namespace MrBill\Apps\Report;

use MrBill\Domain\Conversation;
use MrBill\Domain\DomainFactory;
use MrBill\Domain\ExpenseRecord;
use MrBill\PhoneNumber;

class Report1
{
    /** @var Conversation */
    protected $conversation;

    public function __construct(DomainFactory $conversationFactory, array $get)
    {
        if (empty($get['phone']))
            return; // TODO look for token

        $phone = new PhoneNumber($get['phone']);
        $this->conversation = $conversationFactory->getConversation($phone);
    }

    public function hasInitializationError() : bool
    {
        return !$this->conversation;
    }

    public function getDateText() : string
    {
        $format = 'M jS, Y';

        return date($format, $this->conversation->firstExpenseMessageTimestamp) . ' &mdash; ' .
            date($format, $this->conversation->lastExpenseMessageTimestamp);
    }

    public function getTableContents() : string
    {
        if ($this->hasInitializationError())
            return '';


        $result = '';
        foreach ($this->getDataForTable() as $key => $amound) {
            /** @var ExpenseRecord $expenseRecord */
            $result .= '<tr><td>' . $key . '</td><td>' . $amound . "</td>\n";
        }
        return $result;
    }

    protected function getDataForTable() : array
    {
        $data = [];

        foreach ($this->conversation->getAllExpenseRecords() as $expenseRecord) {
            /** @var ExpenseRecord $expenseRecord */
            $key = $expenseRecord->getHashtagsCanonical();
            if (isset($data[$key])) {
                $data[$key] += $expenseRecord->amount;
            } else {
                $data[$key] = $expenseRecord->amount;
            }
        }
        ksort($data);

        return $data;
    }
}
