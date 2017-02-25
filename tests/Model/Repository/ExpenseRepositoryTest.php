<?php

namespace MrBillTest\Model\Repository;

use MrBill\Model\Expense;
use MrBill\Model\Repository\ExpenseRepository;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class ExpenseRepositoryTest extends TestCase
{
    /** @var int */
    private $time;

    private $year, $month;

    /** @var PhoneNumber */
    private $phone;

    /** @var Expense */
    private $expense1;

    /** @var Expense */
    private $expense2;

    /** @var ExpenseRepository */
    private $expenseRepository;

    public function setUp()
    {
        $this->time = time();
        $this->year = (int) date('Y', $this->time);
        $this->month = (int) date('M', $this->time);

        $this->phone = new PhoneNumber(14087226296);

        $this->expense1 = new Expense(
            $this->phone,
            $this->time,
            599,
            ['hash', 'tag'],
            'description',
            Expense::SOURCE_TYPE_MESSAGE,
            sha1('x'),
            7
        );

        $this->expense2 = new Expense(
            $this->phone,
            $this->time,
            1370,
            ['a', 'b'],
            'description2',
            Expense::SOURCE_TYPE_MESSAGE,
            sha1('y'),
            9
        );

        $this->expenseRepository = new ExpenseRepository(new DataStore());
    }

    public function testRemoveAndPutAndGet()
    {
        $this->expenseRepository->removeForPhoneAndMonth($this->phone, $this->year, $this->month);

        $this->expenseRepository->putForPhoneAndMonth($this->phone, $this->year, $this->month, [
            $this->expense1,
            $this->expense2,
        ]);

        $fetched = $this->expenseRepository->getForPhoneAndMonth($this->phone, $this->year, $this->month);
        $this->assertCount(2, $fetched);
        $this->assertEquals($this->expense1, $fetched[0]);
        $this->assertEquals($this->expense2, $fetched[1]);
    }

    public function testRemoveAndPersistAndGet()
    {
        $this->expenseRepository->removeForPhoneAndMonth($this->phone, $this->year, $this->month);

        $this->expenseRepository->persist($this->expense1);
        $this->expenseRepository->persist($this->expense2);

        $fetched = $this->expenseRepository->getForPhoneAndMonth($this->phone, $this->year, $this->month);
        $this->assertCount(2, $fetched);
        $this->assertEquals($this->expense1, $fetched[0]);
        $this->assertEquals($this->expense2, $fetched[1]);
    }

    public function putAndRemoveAndGet()
    {
        $this->expenseRepository->putForPhoneAndMonth($this->phone, $this->year, $this->month, [
            $this->expense1,
            $this->expense2,
        ]);

        $this->expenseRepository->removeForPhoneAndMonth($this->phone, $this->year, $this->month);

        $fetched = $this->expenseRepository->getForPhoneAndMonth($this->phone, $this->year, $this->month);
        $this->assertCount(0, $fetched);
    }
}
