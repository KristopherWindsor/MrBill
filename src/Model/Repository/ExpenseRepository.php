<?php

namespace MrBill\Model\Repository;

use MrBill\Model\Expense;
use MrBill\PhoneNumber;

class ExpenseRepository extends Repository
{
    public function persist(Expense $expense)
    {
        $phone = $expense->phone;
        $year = (int) date('Y', $expense->timestamp);
        $month = (int) date('n', $expense->timestamp);

        $expenses = $this->getForPhoneAndMonth($phone, $year, $month);
        $expenses[] = $expense;
        $this->putForPhoneAndMonth($phone, $year, $month, $expenses);
    }

    public function putForPhoneAndMonth(PhoneNumber $phoneNumber, int $year, int $month, array $expenses) : void
    {
        $key = $this->getDataStoreKey($phoneNumber, $year, $month);

        $maps = [];
        foreach ($expenses as $expense) {
            assert($expense instanceof Expense);
            $maps[] = $expense->toMap();

        }
        $this->dataStore->put($key, json_encode($maps));
    }

    public function getForPhoneAndMonth(PhoneNumber $phoneNumber, int $year, int $month) : array
    {
        $key = $this->getDataStoreKey($phoneNumber, $year, $month);

        $jsonArray = json_decode($this->dataStore->get($key)->current(), true);

        if (!$jsonArray) return [];

        return array_map(
            function ($item) {return Expense::createFromMap($item);},
            $jsonArray
        );
    }

    public function removeForPhoneAndMonth(PhoneNumber $phoneNumber, int $year, int $month) : void
    {
        $key = $this->getDataStoreKey($phoneNumber, $year, $month);

        $this->dataStore->remove($key);
    }

    protected function getDataStoreKey(PhoneNumber $phone, int $year, int $month) : string
    {
        return 'expenses' . $phone . '_' . $year . '_' . ($month < 10 ? '0' : '') . $month;
    }
}
