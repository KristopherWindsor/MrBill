<?php

namespace MrBill\Model\Repository;

use Generator;
use MrBill\Model\Expense;
use MrBill\PhoneNumber;

class ExpenseRepository extends Repository
{
    public function persist(Expense $expense) : void
    {
        $key = $this->getDataStoreKey($expense->phone);
        $this->dataStore->append($key, $expense->toJson());
    }

    public function getAllForPhone(PhoneNumber $phoneNumber) : Generator
    {
        $key = $this->getDataStoreKey($phoneNumber);
        foreach ($this->dataStore->get($key) as $item)
            yield Expense::createFromJson($item);
    }

    public function removeAllForPhone(PhoneNumber $phoneNumber) : void
    {
        $key = $this->getDataStoreKey($phoneNumber);

        $this->dataStore->remove($key);
    }

    protected function getDataStoreKey(PhoneNumber $phone) : string
    {
        return 'expenses' . $phone;
    }
}
