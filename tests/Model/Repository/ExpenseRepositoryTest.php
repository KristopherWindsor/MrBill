<?php

namespace MrBillTest\Model\Repository;

use MrBill\Model\Expense;
use MrBill\Model\Repository\ExpenseRepository;
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
            Expense::SOURCE_TYPE_MESSAGE,
            ['inf'],
            7
        );

        $this->expense2 = new Expense(
            $this->phone,
            $this->time,
            1370,
            ['a', 'b'],
            'description2',
            Expense::SOURCE_TYPE_MESSAGE,
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

    public function testPutForPhoneAndMonth()
    {
        $this->expenseRepository->putForPhoneAndMonth($this->phone, $this->year, $this->month, [
            $this->expense1,
            $this->expense2,
        ]);

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
        $this->assertEquals($this->expense1, $fetched[0]);
        $this->assertEquals($this->expense2, $fetched[1]);
    }

    public function testRemoveForPhoneAndMonth()
    {
        $this->testPersistOneTime();

        $this->expenseRepository->removeForPhoneAndMonth($this->phone, $this->year, $this->month);

        $this->assertEmpty(
            $this->mockDataStore->storage
        );
    }

    protected function getStorageOfOneExpense()
    {
        return
            [
                'expenses14087226296_2017_02' => [
                    '[{"phone":14087226296,"timestamp":1488012941,"amountInCents":599,"hashTags":["hash","tag"],' .
                    '"description":"description","sourceType":"_m","sourceInfo":' .
                    '["inf"],"entropy":"7"}]'
                ]
            ];
    }

    protected function getStorageOfBothExpenses()
    {
        return
            [
                'expenses14087226296_2017_02' => [
                    '[{"phone":14087226296,"timestamp":1488012941,"amountInCents":599,"hashTags":["hash","tag"],' .
                    '"description":"description","sourceType":"_m","sourceInfo":' .
                    '["inf"],"entropy":"7"},' .
                    '{"phone":14087226296,"timestamp":1488012941,"amountInCents":1370,"hashTags":["a","b"],' .
                    '"description":"description2","sourceType":"_m","sourceInfo":' .
                    '["inf"],"entropy":"9"}]'
                ]
            ];
    }
}
