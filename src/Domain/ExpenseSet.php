<?php

namespace MrBill\Domain;

use Generator;
use MrBill\Model\Expense;
use MrBill\Model\Repository\ExpenseRepository;
use MrBill\PhoneNumber;

class ExpenseSet
{
    const CENTS_PER_DOLLAR = 100;

    /** @var int */
    protected $accountId;

    /** @var ExpenseRepository */
    protected $expenseRepository;

    public function __construct(
        int $accountId,
        ExpenseRepository $expenseRepository
    ) {
        $this->accountId = $accountId;
        $this->expenseRepository = $expenseRepository;
    }

    public function addExpense(Expense $expense)
    {
        $this->expenseRepository->persist($expense);
    }

    public function getBoundaryOfMonthsWithExpenses() : array
    {
        return $this->expenseRepository->getRangeOfMonthsWithData($this->accountId);
    }

    public function getAllMonthsWithExpenses() : array
    {
        $rangeData = $this->expenseRepository->getRangeOfMonthsWithData($this->accountId);

        return $this->getAllMonthsWithExpensesHelper($rangeData);
    }

    protected function getAllMonthsWithExpensesHelper(?array $rangeData) : array
    {
        if (!$rangeData)
            return [];

        $results = [];
        for ($year = $rangeData['firstYear']; $year <= $rangeData['lastYear']; $year++)
            for ($month = 1; $month <= 12; $month++)
                if ($year > $rangeData['firstYear'] || $month >= $rangeData['firstMonth'])
                    if ($year < $rangeData['lastYear'] || $month <= $rangeData['lastMonth'])
                        $results[] = [$year, $month];

        return $results;
    }

    public function getAllExpenses() : Generator
    {
        foreach ($this->getAllMonthsWithExpenses() as list($year, $month)) {
            $expenses = $this->getExpensesForMonth($year, $month);
            foreach ($expenses as $id => $expense)
                yield [$id, $expense];
            unset($expenses);
        }
    }

    public function getExpensesForMonth(int $year, int $month) : array
    {
        $results = $this->expenseRepository->getForAccountAndMonth($this->accountId, $year, $month);
        ksort($results, SORT_NUMERIC);
        return $results;
    }
}
