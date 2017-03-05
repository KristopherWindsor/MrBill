<?php

namespace MrBill\Model\Repository;

use MrBill\Model\Expense;
use MrBill\PhoneNumber;

class ExpenseRepository extends Repository
{
    public function persist(Expense $expense) : int
    {
        $phone = $expense->phone;
        $year = (int) date('Y', $expense->timestamp);
        $month = (int) date('n', $expense->timestamp);

        return $this->addForPhoneAndMonth($phone, $year, $month, $expense);
    }

    protected function addForPhoneAndMonth(PhoneNumber $phoneNumber, int $year, int $month, Expense $expense) : int
    {
        $this->updateRangeOfMonthsData($phoneNumber, $year, $month);

        $id = $this->incrementAndGetId($phoneNumber);
        $this->setMonthForId($phoneNumber, $id, $year, $month);

        $this->dataStore->mapPutItem(
            $this->getDataStoreKey($phoneNumber, $year, $month),
            $id,
            json_encode($expense->toMap())
        );
        return $id;
    }

    protected function updateRangeOfMonthsData(PhoneNumber $phoneNumber, int $year, int $month) : void
    {
        $data = $this->getRangeOfMonthsWithData($phoneNumber);
        $key = $this->getMetaDataKey($phoneNumber);

        if (!$data) {
            $this->dataStore->mapPutItem($key, 'firstYear', $year);
            $this->dataStore->mapPutItem($key, 'firstMonth', $month);
            $this->dataStore->mapPutItem($key, 'lastYear', $year);
            $this->dataStore->mapPutItem($key, 'lastMonth', $month);
        } elseif ($year * 100 + $month < $data['firstYear'] * 100 + $data['firstMonth']) {
            $this->dataStore->mapPutItem($key, 'firstYear', $year);
            $this->dataStore->mapPutItem($key, 'firstMonth', $month);
        } elseif ($year * 100 + $month > $data['lastYear'] * 100 + $data['lastMonth']) {
            $this->dataStore->mapPutItem($key, 'lastYear', $year);
            $this->dataStore->mapPutItem($key, 'lastMonth', $month);
        }
    }

    protected function setMonthForId(PhoneNumber $phoneNumber, int $id, int $year, int $month)
    {
        $key = $this->getIdToMonthMapKey($phoneNumber);
        $this->dataStore->mapPutItem($key, $id, $year . ($month < 10 ? '0' : '') . $month);
    }

    /**
     * @param PhoneNumber $phoneNumber
     * @param int $year
     * @param int $month
     * @return Expense[] assoc array with ids as keys
     */
    public function getForPhoneAndMonth(PhoneNumber $phoneNumber, int $year, int $month) : array
    {
        $key = $this->getDataStoreKey($phoneNumber, $year, $month);

        return array_map(
            function ($item) {return Expense::createFromMap(json_decode($item, true));},
            $this->dataStore->mapGetAll($key)
        );
    }

    public function getRangeOfMonthsWithData(PhoneNumber $phone) : ?array
    {
        $key = $this->getMetaDataKey($phone);
        $meta = $this->dataStore->mapGetAll($key);

        if (empty($meta['firstYear'])) {
            return null;
        }

        return [
            'firstYear'  => (int) $meta['firstYear'],
            'firstMonth' => (int) $meta['firstMonth'],
            'lastYear'   => (int) $meta['lastYear'],
            'lastMonth'  => (int) $meta['lastMonth'],
        ];
    }

    protected function incrementAndGetId(PhoneNumber $phone) : int
    {
        $key = $this->getMetaDataKey($phone);
        return $this->dataStore->mapIncrementItem($key, 'id');
    }

    protected function getMetaDataKey(PhoneNumber $phone)
    {
        return 'expenses:' . $phone . ':meta';
    }

    protected function getIdToMonthMapKey(PhoneNumber $phone)
    {
        return 'expenses:' . $phone . ':map';
    }

    protected function getDataStoreKey(PhoneNumber $phone, int $year, int $month) : string
    {
        return 'expenses:' . $phone . ':' . $year . ':' . ($month < 10 ? '0' : '') . $month;
    }
}
