<?php

namespace MrBill\Model\Repository;

use MrBill\Model\Expense;
use MrBill\PhoneNumber;

class ExpenseRepository extends Repository
{
    public function persist(Expense $expense) : int
    {
        $phone = $expense->accountId;
        $year = (int) date('Y', $expense->timestamp);
        $month = (int) date('n', $expense->timestamp);

        return $this->addForAccountAndMonth($phone, $year, $month, $expense);
    }

    protected function addForAccountAndMonth(int $accountId, int $year, int $month, Expense $expense) : int
    {
        $this->updateRangeOfMonthsData($accountId, $year, $month);

        $id = $this->incrementAndGetId($accountId);
        $this->setMonthForId($accountId, $id, $year, $month);

        $this->dataStore->mapPutItem(
            $this->getDataStoreKey($accountId, $year, $month),
            $id,
            json_encode($expense->toMap())
        );
        return $id;
    }

    protected function updateRangeOfMonthsData(int $accountId, int $year, int $month) : void
    {
        $data = $this->getRangeOfMonthsWithData($accountId);
        $key = $this->getMetaDataKey($accountId);

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

    protected function setMonthForId(int $accountId, int $id, int $year, int $month)
    {
        $key = $this->getIdToMonthMapKey($accountId);
        $this->dataStore->mapPutItem($key, $id, $year . ($month < 10 ? '0' : '') . $month);
    }

    /**
     * @param int $accountId
     * @param int $year
     * @param int $month
     * @return Expense[] assoc array with ids as keys
     */
    public function getForAccountAndMonth(int $accountId, int $year, int $month) : array
    {
        $key = $this->getDataStoreKey($accountId, $year, $month);

        return array_map(
            function ($item) {return Expense::createFromMap(json_decode($item, true));},
            $this->dataStore->mapGetAll($key)
        );
    }

    public function getRangeOfMonthsWithData(int $accountId) : ?array
    {
        $key = $this->getMetaDataKey($accountId);
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

    protected function incrementAndGetId(int $accountId) : int
    {
        $key = $this->getMetaDataKey($accountId);
        return $this->dataStore->mapIncrementItem($key, 'id');
    }

    protected function getMetaDataKey(int $accountId)
    {
        return 'expenses:' . $accountId . ':meta';
    }

    protected function getIdToMonthMapKey(int $accountId)
    {
        return 'expenses:' . $accountId . ':map';
    }

    protected function getDataStoreKey(int $accountId, int $year, int $month) : string
    {
        return 'expenses:' . $accountId . ':' . $year . ':' . ($month < 10 ? '0' : '') . $month;
    }
}
