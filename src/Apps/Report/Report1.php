<?php

namespace MrBill\Apps\Report;

use MrBill\Domain\Conversation;
use MrBill\Domain\DomainFactory;
use MrBill\Domain\ExpensesFromMessageParser;
use MrBill\Model\Expense;
use MrBill\PhoneNumber;

class Report1
{
    /** @var Conversation */
    protected $conversation;

    public function __construct(DomainFactory $domainFactory, array $get)
    {
        if (empty($get['p']) || empty($get['s']))
            return;

        $phone = new PhoneNumber($get['p']);
        $conversation = $domainFactory->getConversation($phone);

        $token = $conversation->getExistingReportToken();
        if (!$token || $token->isExpired() || $get['s'] != $token->secret)
            return;

        $this->conversation = $conversation;
    }

    public function hasInitializationError() : bool
    {
        return !$this->conversation;
    }

    public function getDateText() : string
    {
        if ($this->hasInitializationError())
            return '';

        $format = 'M jS, Y';

        return date($format, $this->conversation->firstExpenseMessageTimestamp) . ' &mdash; ' .
            date($format, $this->conversation->lastExpenseMessageTimestamp);
    }

    public function getTableContents() : string
    {
        if ($this->hasInitializationError())
            return '';


        $result = '';
        foreach ($this->getDataForTable() as $key => $amount) {
            $result .= '<tr><td>' . $key . '</td><td>' . $amount . "</td>\n";
        }
        return $result;
    }

    protected function getDataForTable() : array
    {
        $data = [];

        foreach ($this->conversation->getAllExpenseRecords() as $expenseRecord) {
            /** @var Expense $expenseRecord */
            $key = '#' . implode('#', $expenseRecord->hashTags);
            if (isset($data[$key])) {
                $data[$key] += $expenseRecord->amountInCents / 100; // TODO some refactoring..
            } else {
                $data[$key] = $expenseRecord->amountInCents / 100;
            }
        }
        ksort($data);

        return $data;
    }
}
