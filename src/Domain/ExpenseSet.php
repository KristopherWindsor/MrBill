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

    public function getExpenses() : Generator
    {
        // TODO use all months, not just current month
        foreach ($this->expenseRepository->getForPhoneAndMonth($this->phone, 2017, 3) as $expense)
            /** @var Expense $expense */
            yield $expense;
    }
}
