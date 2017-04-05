<?php

namespace MrBillTest\Unit\Model\Repository;

use MrBill\Model\Expense;
use MrBill\Model\Repository\ExpenseRepository;
use MrBill\Persistence\MockDataStore;
use PHPUnit\Framework\TestCase;

class ExpenseRepositoryTest extends TestCase
{
    const TEST_ACCOUNT_ID = 123;
    const TEST_EXPENSE_ID = 456;

    /** @var int */
    private $time;

    private $year, $month;

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

        $this->expense1 = new Expense(
            self::TEST_ACCOUNT_ID,
            $this->time,
            599,
            ['hash', 'tag'],
            'description',
            null,
            Expense::STATUS_FROM_MESSAGE,
            ['inf']
        );

        $this->expense2 = new Expense(
            self::TEST_ACCOUNT_ID,
            $this->time,
            1370,
            ['a', 'b'],
            'description2',
            null,
            Expense::STATUS_FROM_MESSAGE,
            ['inf']
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
        $id1 = $this->expenseRepository->persist($this->expense1);
        $id2 = $this->expenseRepository->persist($this->expense2);

        $this->assertEquals(1, $id1);
        $this->assertEquals(2, $id2);

        $this->assertEquals(
            $this->getStorageOfBothExpenses(),
            $this->mockDataStore->storage
        );
    }

    public function testDeleteByIdNoItem()
    {
        $this->expectException(\Exception::class);
        $this->expenseRepository->deleteById(self::TEST_ACCOUNT_ID, 1);
    }

    public function testDeleteByIdGoRight()
    {
        $mockDataStore = $this->getMockBuilder(MockDataStore::class)
            ->disableOriginalConstructor()
            ->setMethods(['mapGetItem', 'mapRemoveItem'])
            ->getMock();

        $mockDataStore->expects($this->once())
            ->method('mapGetItem')
            ->with('expenses:123:map', '456')
            ->willReturn('201702');
        $mockDataStore->expects($this->exactly(2))
            ->method('mapRemoveItem')
            ->with($this->stringContains('expenses'), '456');

        $expenseRepository = new ExpenseRepository($mockDataStore);
        $expenseRepository->deleteById(self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID);
    }

    public function testAddForAccountAndMonth()
    {
        $id1 = $this->callAddForAccountAndMonth(
            $this->expenseRepository,
            self::TEST_ACCOUNT_ID, $this->year, $this->month, $this->expense1
        );
        $id2 = $this->callAddForAccountAndMonth(
            $this->expenseRepository,
            self::TEST_ACCOUNT_ID, $this->year, $this->month, $this->expense2
        );

        $this->assertEquals(1, $id1);
        $this->assertEquals(2, $id2);

        $this->assertEquals(
            $this->getStorageOfBothExpenses(),
            $this->mockDataStore->storage
        );
    }

    public function testGetForAccountAndMonthNoResults()
    {
        $fetched = $this->expenseRepository->getForAccountAndMonth(self::TEST_ACCOUNT_ID, $this->year, $this->month);
        $this->assertCount(0, $fetched);
    }

    public function testGetForAccountAndMonthWithResults()
    {
        $this->testPersistMultipleTimes();

        $fetched = $this->expenseRepository->getForAccountAndMonth(self::TEST_ACCOUNT_ID, $this->year, $this->month);

        $this->assertCount(2, $fetched);
        $this->assertEquals($this->expense1, $fetched[1]);
        $this->assertEquals($this->expense2, $fetched[2]);
    }

    protected function getStorageOfOneExpense()
    {
        return
            [
                'expenses:123:2017:02' => [
                    1 =>
                        '{"accountId":123,"timestamp":1488012941,"amountInCents":599,"hashTags":["hash","tag"],' .
                        '"description":"description","depreciation":null,"sourceType":"_m","sourceInfo":' .
                        '["inf"]}',
                ],
                'expenses:123:meta' => [
                    'id' => 1,
                    'firstYear' => '2017',
                    'firstMonth' => '2',
                    'lastYear' => '2017',
                    'lastMonth' => '2',
                ],
                'expenses:123:map' => [
                    '1' => '201702',
                ],
            ];
    }

    protected function getStorageOfBothExpenses()
    {
        return
            [
                'expenses:123:2017:02' => [
                    1 =>
                        '{"accountId":123,"timestamp":1488012941,"amountInCents":599,"hashTags":["hash","tag"],' .
                        '"description":"description","depreciation":null,"sourceType":"_m","sourceInfo":' .
                        '["inf"]}',
                    2 =>
                        '{"accountId":123,"timestamp":1488012941,"amountInCents":1370,"hashTags":["a","b"],' .
                        '"description":"description2","depreciation":null,"sourceType":"_m","sourceInfo":' .
                        '["inf"]}',
                ],
                'expenses:123:meta' => [
                    'id' => 2,
                    'firstYear' => '2017',
                    'firstMonth' => '2',
                    'lastYear' => '2017',
                    'lastMonth' => '2'
                ],
                'expenses:123:map' => [
                    '1' => '201702',
                    '2' => '201702',
                ],
            ];
    }

    protected function callAddForAccountAndMonth(
        ExpenseRepository $object,
        int $accountId,
        int $year,
        int $month,
        Expense $expense
    ) : int {
        $method = new \ReflectionMethod(ExpenseRepository::class, 'addForAccountAndMonth');
        $method->setAccessible(true);

        return $method->invoke($object, $accountId, $year, $month, $expense);
    }
}
