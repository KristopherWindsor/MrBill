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

        $this->addForPhoneAndMonth($phone, $year, $month, $expense);
    }

    protected function addForPhoneAndMonth(PhoneNumber $phoneNumber, int $year, int $month, Expense $expense) : void
    {
        $this->dataStore->mapPutItem(
            $this->getDataStoreKey($phoneNumber, $year, $month),
            $this->incrementAndGetId($phoneNumber, $year, $month),
            json_encode($expense->toMap())
        );
    }

    public function getForPhoneAndMonth(PhoneNumber $phoneNumber, int $year, int $month) : array
    {
        $key = $this->getDataStoreKey($phoneNumber, $year, $month);

        return array_map(
            function ($item) {return Expense::createFromMap(json_decode($item, true));},
            $this->dataStore->mapGetAll($key)
        );
    }

    public function removeForPhoneAndMonth(PhoneNumber $phoneNumber, int $year, int $month) : void
    {
        $key = $this->getDataStoreKey($phoneNumber, $year, $month);

        $this->dataStore->remove($key);
    }

    protected function incrementAndGetId(PhoneNumber $phone, int $year, int $month) : int
    {
        $key = $this->getIdKey($phone, $year, $month);
        return $this->dataStore->scalarIncrement($key);
    }

    protected function getIdKey(PhoneNumber $phone, int $year, int $month)
    {
        return 'expenses:' . $phone . ':' . $year . ':' . ($month < 10 ? '0' : '') . $month . ':id';
    }

    protected function getDataStoreKey(PhoneNumber $phone, int $year, int $month) : string
    {
        return 'expenses:' . $phone . ':' . $year . ':' . ($month < 10 ? '0' : '') . $month;
    }
}
