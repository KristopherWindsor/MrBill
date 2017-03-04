<?php

namespace MrBill\Domain;

use Generator;
use MrBill\Model\Expense;
use MrBill\Model\Repository\ExpenseRepository;
use MrBill\PhoneNumber;

class ExpenseSet
{
    /** @var PhoneNumber */
    protected $phone;

    /** @var ExpenseRepository */
    protected $expenseRepository;

    public function __construct(
        PhoneNumber $phone,
        ExpenseRepository $expenseRepository
    ) {
        $this->phone = $phone;
        $this->expenseRepository = $expenseRepository;
    }

    public function getPhoneNumber()
    {
        return $this->phone;
    }

    public function addExpense(Expense $expense)
    {
        $this->expenseRepository->persist($expense);
    }

    // TODO method to get all expenses for a month-range or all time

    public function getExpensesForMonth(int $year, int $month) : array
    {
        return $this->expenseRepository->getForPhoneAndMonth($this->phone, $year, $month);
    }
}
