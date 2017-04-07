<?php

namespace MrBill\Model\Repository;

use MrBill\Model\Expense;
use MrBill\PhoneNumber;

class ExpenseRepository extends Repository
{
    public function persist(Expense $expense) : int
    {
        $expenseId = $this->incrementAndGetId($expense->accountId);
        $this->persistExpenseForId($expenseId, $expense);
        return $expenseId;
    }

    protected function incrementAndGetId(int $accountId) : int
    {
        $key = $this->getMetaDataKey($accountId);
        return $this->dataStore->mapIncrementItem($key, 'id');
    }

    protected function persistExpenseForId(int $expenseId, Expense $expense) : void
    {
        $accountId = $expense->accountId;
        list($year, $month) = $this->getYearAndMonthForExpense($expense);

        $this->updateRangeOfMonthsData($accountId, $year, $month);

        $this->setMonthForId($accountId, $expenseId, $year, $month);

        $this->dataStore->mapPutItem(
            $this->getDataStoreKey($accountId, $year, $month),
            $expenseId,
            json_encode($expense->toMap())
        );
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

    protected function setMonthForId(int $accountId, int $id, int $year, int $month)
    {
        $key = $this->getIdToMonthMapKey($accountId);
        $this->dataStore->mapPutItem($key, $id, $year . ($month < 10 ? '0' : '') . $month);
    }

    public function updateIfExists(int $accountId, int $expenseId, Expense $expense) : bool
    {
        $existingExpense = $this->getById($accountId, $expenseId);
        if (!$existingExpense)
            return false;

        $newYearAndMonth = $this->getYearAndMonthForExpense($expense);
        $oldYearAndMonth = $this->getYearAndMonthForExpense($existingExpense);
        if ($newYearAndMonth != $oldYearAndMonth) {
            $this->deleteById($accountId, $expenseId);
        }

        $this->persistExpenseForId($expenseId, $expense);
        return true;
    }

    protected function getById(int $accountId, int $expenseId) : ?Expense
    {
        list($year, $month) = $this->getMonthAndYearForId($accountId, $expenseId);

        $key = $this->getDataStoreKey($accountId, $year, $month);
        $data = $this->dataStore->mapGetItem($key, $expenseId);

        if (!$data) return null;

        return Expense::createFromMap(json_decode($data, true));
    }

    protected function getMonthAndYearForId(int $accountId, int $expenseId) : ?array
    {
        $key = $this->getIdToMonthMapKey($accountId);
        $value = $this->dataStore->mapGetItem($key, $expenseId);

        if (!$value) return null;

        $year = (int) substr($value, 0, 4);
        $month = (int) substr($value, 4, 2);
        return [$year, $month];
    }

    protected function getYearAndMonthForExpense(Expense $expense)
    {
        $year = (int) date('Y', $expense->timestamp);
        $month = (int) date('n', $expense->timestamp);

        return [$year, $month];
    }

    public function deleteById(int $accountId, int $expenseId)
    {
        $key = $this->getIdToMonthMapKey($accountId);
        $monthAndYear = $this->dataStore->mapGetItem($key, $expenseId);

        if (!$monthAndYear)
            throw new \Exception();
        assert(strlen($monthAndYear) == 6);

        $this->dataStore->mapRemoveItem($key, $expenseId);

        $year = (int) substr($monthAndYear, 0, 4);
        $month = (int) substr($monthAndYear, 4, 2);
        $this->dataStore->mapRemoveItem(
            $this->getDataStoreKey($accountId, $year, $month),
            $expenseId
        );
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
