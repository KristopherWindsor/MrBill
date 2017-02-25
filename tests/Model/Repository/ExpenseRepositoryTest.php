<?php

namespace MrBill\Model\Repository;

use MrBill\Model\Expense;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class ExpenseRepositoryTest extends TestCase
{
    /** @var int */
    private $time;

    /** @var PhoneNumber */
    private $phone;

    /** @var Expense */
    private $expense;

    /** @var ExpenseRepository */
    private $expenseRepository;

    public function setUp()
    {
        $this->time = time();

        $this->phone = new PhoneNumber(14087226296);

        $this->expense = new Expense(
            $this->phone,
            $this->time,
            599,
            ['hash', 'tag'],
            'description',
            Expense::SOURCE_TYPE_MESSAGE,
            sha1('x'),
            7
        );

        $this->expenseRepository = new ExpenseRepository(new DataStore());
    }

    public function testRemoveAllAndPersistAndGetAll()
    {
        $phone = $this->expense->phone;

        $this->expenseRepository->removeAllForPhone($phone);

        for ($i = 0; $i < 2; $i++)
            $this->expenseRepository->persist($this->expense);

        $expenses = iterator_to_array(
            $this->expenseRepository->getAllForPhone($phone)
        );

        $this->assertCount(2, $expenses);
        foreach ($expenses as $expense)
            $this->assertEquals($this->expense, $expense);
    }

    public function testPersistAndRemoveAllAndGetAll()
    {
        $phone = $this->expense->phone;

        for ($i = 0; $i < 2; $i++)
            $this->expenseRepository->persist($this->expense);

        $this->expenseRepository->removeAllForPhone($phone);

        $expenses = iterator_to_array(
            $this->expenseRepository->getAllForPhone($phone)
        );

        $this->assertCount(0, $expenses);
    }
}
