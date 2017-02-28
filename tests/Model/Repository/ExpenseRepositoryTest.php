<?php

namespace MrBillTest\Model\Repository;

use MrBill\Model\Expense;
use MrBill\Model\Repository\ExpenseRepository;
use MrBill\Persistence\MockDataStore;
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

    /** @var MockDataStore */
    private $mockDataStore;

    /** @var ExpenseRepository */
    private $expenseRepository;

    public function setUp()
    {
        $this->time = 1488012941;
        $this->year = (int) date('Y', $this->time);
        $this->month = (int) date('n', $this->time);

        $this->phone = new PhoneNumber(14087226296);

        $this->expense1 = new Expense(
            $this->phone,
            $this->time,
            599,
            ['hash', 'tag'],
            'description',
            Expense::STATUS_FROM_MESSAGE,
            ['inf'],
            7
        );

        $this->expense2 = new Expense(
            $this->phone,
            $this->time,
            1370,
            ['a', 'b'],
            'description2',
            Expense::STATUS_FROM_MESSAGE,
            ['inf'],
            9
        );

        $this->mockDataStore = new MockDataStore();

        $this->expenseRepository = new ExpenseRepository($this->mockDataStore);
    }

    public function testPersistOneTime()
    {
        $this->expenseRepository->persist($this->expense1);

        $this->assertEquals(
            $this->getStorageOfOneExpense(),
            $this->mockDataStore->storage
        );
    }

    public function testPersistMultipleTimes()
    {
        $this->expenseRepository->persist($this->expense1);
        $this->expenseRepository->persist($this->expense2);

        $this->assertEquals(
            $this->getStorageOfBothExpenses(),
            $this->mockDataStore->storage
        );
    }

    public function testAddForPhoneAndMonth()
    {
        $this->callAddForPhoneAndMonth(
            $this->expenseRepository,
            $this->phone, $this->year, $this->month, $this->expense1
        );
        $this->callAddForPhoneAndMonth(
            $this->expenseRepository,
            $this->phone, $this->year, $this->month, $this->expense2
        );

        $this->assertEquals(
            $this->getStorageOfBothExpenses(),
            $this->mockDataStore->storage
        );
    }

    public function testGetForPhoneAndMonthNoResults()
    {
        $fetched = $this->expenseRepository->getForPhoneAndMonth($this->phone, $this->year, $this->month);
        $this->assertCount(0, $fetched);
    }

    public function testGetForPhoneAndMonthWithResults()
    {
        $this->testPersistMultipleTimes();

        $fetched = $this->expenseRepository->getForPhoneAndMonth($this->phone, $this->year, $this->month);

        $this->assertCount(2, $fetched);
        $this->assertEquals($this->expense1, $fetched[1]);
        $this->assertEquals($this->expense2, $fetched[2]);
    }

    public function testRemoveForPhoneAndMonth()
    {
        $this->testPersistOneTime();

        $this->expenseRepository->removeForPhoneAndMonth($this->phone, $this->year, $this->month);

        $this->assertTrue(
            !isset($this->mockDataStore->storage['expenses:' . $this->phone . ':' . $this->year . ':' . $this->month])
        );
    }

    protected function getStorageOfOneExpense()
    {
        return
            [
                'expenses:14087226296:2017:02' => [
                    1 =>
                        '{"phone":14087226296,"timestamp":1488012941,"amountInCents":599,"hashTags":["hash","tag"],' .
                        '"description":"description","sourceType":"_m","sourceInfo":' .
                        '["inf"],"entropy":"7"}',
                ],
                'expenses:14087226296:2017:02:id' => 1
            ];
    }

    protected function getStorageOfBothExpenses()
    {
        return
            [
                'expenses:14087226296:2017:02' => [
                    1 =>
                        '{"phone":14087226296,"timestamp":1488012941,"amountInCents":599,"hashTags":["hash","tag"],' .
                        '"description":"description","sourceType":"_m","sourceInfo":' .
                        '["inf"],"entropy":"7"}',
                    2 =>
                        '{"phone":14087226296,"timestamp":1488012941,"amountInCents":1370,"hashTags":["a","b"],' .
                        '"description":"description2","sourceType":"_m","sourceInfo":' .
                        '["inf"],"entropy":"9"}',
                ],
                'expenses:14087226296:2017:02:id' => 2
            ];
    }

    protected function callAddForPhoneAndMonth(
        ExpenseRepository $object,
        PhoneNumber $phoneNumber,
        int $year,
        int $month,
        Expense $expense
    ) : void {
        $method = new \ReflectionMethod(ExpenseRepository::class, 'addForPhoneAndMonth');
        $method->setAccessible(true);

        $method->invoke($object, $phoneNumber, $year, $month, $expense);
    }
}
