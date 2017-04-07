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

    /** @var \PHPUnit_Framework_MockObject_MockBuilder */
    private $mockRepositoryBuilder;

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

        $this->mockRepositoryBuilder = $this
            ->getMockBuilder(ExpenseRepository::class)
            ->setConstructorArgs([$this->mockDataStore]);

        $this->expenseRepository = new ExpenseRepository($this->mockDataStore);
    }

    public function testPersist()
    {
        $mock = $this->mockRepositoryBuilder
            ->setMethods(['incrementAndGetId', 'persistExpenseForId'])
            ->getMock();

        $mock->expects($this->once())
            ->method('incrementAndGetId')
            ->with($this->expense1->accountId)
            ->willReturn(1);

        $mock->expects($this->once())
            ->method('persistExpenseForId')
            ->with(1, $this->expense1)
            ->willReturn(1);

        $mock->persist($this->expense1);
    }

    public function testIncrementAndGetId()
    {
        $mock = $this->mockRepositoryBuilder
            ->setMethods(['getMetaDataKey'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getMetaDataKey')
            ->with(self::TEST_ACCOUNT_ID)
            ->willReturn('aaa');

        $incrementAndGetId = new \ReflectionMethod(ExpenseRepository::class, 'incrementAndGetId');
        $incrementAndGetId->setAccessible(true);
        $incrementAndGetId->invoke($mock, self::TEST_ACCOUNT_ID);
    }

    public function testPersistExpenseForId()
    {
        $mock = $this->mockRepositoryBuilder
            ->setMethods([
                'getYearAndMonthForExpense',
                'updateRangeOfMonthsData',
                'setMonthForId',
                'getDataStoreKey',
            ])
            ->getMock();

        $mock->expects($this->once())
            ->method('getYearAndMonthForExpense')
            ->with($this->expense1)
            ->willReturn([2000, 12]);

        $mock->expects($this->once())
            ->method('updateRangeOfMonthsData')
            ->with($this->expense1->accountId, 2000, 12);

        $mock->expects($this->once())
            ->method('setMonthForId')
            ->with($this->expense1->accountId, self::TEST_EXPENSE_ID, 2000, 12);

        $mock->expects($this->once())
            ->method('getDataStoreKey')
            ->with(self::TEST_ACCOUNT_ID, 2000, 12)
            ->willReturn('somekey');

        $persistExpenseForId = new \ReflectionMethod(ExpenseRepository::class, 'persistExpenseForId');
        $persistExpenseForId->setAccessible(true);
        $persistExpenseForId->invoke($mock, self::TEST_EXPENSE_ID, $this->expense1);

        $this->assertEquals(
            ['somekey' => [self::TEST_EXPENSE_ID => json_encode($this->expense1->toMap())]],
            $this->mockDataStore->storage
        );
    }

    public function testUpdateRangeOfMonthsData()
    {
        //TODO
    }

    public function testGetRangeOfMonthsWithData()
    {
        // TODO
    }

    public function testSetMonthForId()
    {
        // TODO
    }

    public function testUpdateIfExistsVsMissing()
    {
        $mock = $this->mockRepositoryBuilder
            ->setMethods(['getById'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getById')
            ->with(self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID)
            ->willReturn(null);

        $updateIfExists = new \ReflectionMethod(ExpenseRepository::class, 'updateIfExists');
        $updateIfExists->setAccessible(true);
        $result = $updateIfExists->invoke($mock, self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID, $this->expense1);

        $this->assertFalse($result);
    }

    public function testUpdateIfExistsVsMonthChanged()
    {
        $updatedExpense = clone $this->expense1;
        $updatedExpense->timestamp += 86400 * 45;

        $mock = $this->mockRepositoryBuilder
            ->setMethods(['getById', 'deleteById', 'persistExpenseForId'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getById')
            ->with(self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID)
            ->willReturn($this->expense1);

        $mock->expects($this->once())
            ->method('deleteById')
            ->with(self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID);

        $mock->expects($this->once())
            ->method('persistExpenseForId')
            ->with(self::TEST_EXPENSE_ID, $updatedExpense);

        $updateIfExists = new \ReflectionMethod(ExpenseRepository::class, 'updateIfExists');
        $updateIfExists->setAccessible(true);
        $result = $updateIfExists->invoke($mock, self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID, $updatedExpense);

        $this->assertTrue($result);
    }

    public function testUpdateIfExistsVsMonthNotChanged()
    {
        $updatedExpense = clone $this->expense1;

        $mock = $this->mockRepositoryBuilder
            ->setMethods(['getById', 'deleteById', 'persistExpenseForId'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getById')
            ->with(self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID)
            ->willReturn($this->expense1);

        $mock->expects($this->never())
            ->method('deleteById');

        $mock->expects($this->once())
            ->method('persistExpenseForId')
            ->with(self::TEST_EXPENSE_ID, $updatedExpense);

        $updateIfExists = new \ReflectionMethod(ExpenseRepository::class, 'updateIfExists');
        $updateIfExists->setAccessible(true);
        $result = $updateIfExists->invoke($mock, self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID, $updatedExpense);

        $this->assertTrue($result);
    }

    public function testGetByIdVsMissing()
    {
        $mock = $this->mockRepositoryBuilder
            ->setMethods(['getMonthAndYearForId', 'getDataStoreKey'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getMonthAndYearForId')
            ->with(self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID)
            ->willReturn([2000, 12]);

        $mock->expects($this->once())
            ->method('getDataStoreKey')
            ->with(self::TEST_ACCOUNT_ID, 2000, 12)
            ->willReturn('key');

        $getById = new \ReflectionMethod(ExpenseRepository::class, 'getById');
        $getById->setAccessible(true);
        $result = $getById->invoke($mock, self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID);

        $this->assertNull($result);
    }

    public function testGetById()
    {
        $this->mockDataStore->mapPutItem(
            'key',
            self::TEST_EXPENSE_ID,
            json_encode($this->expense1->toMap())
        );

        $mock = $this->mockRepositoryBuilder
            ->setMethods(['getMonthAndYearForId', 'getDataStoreKey'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getMonthAndYearForId')
            ->with(self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID)
            ->willReturn([2000, 12]);

        $mock->expects($this->once())
            ->method('getDataStoreKey')
            ->with(self::TEST_ACCOUNT_ID, 2000, 12)
            ->willReturn('key');

        $getById = new \ReflectionMethod(ExpenseRepository::class, 'getById');
        $getById->setAccessible(true);
        $result = $getById->invoke($mock, self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID);

        $this->assertNotEmpty($result);
        $this->assertEquals($this->expense1, $result);
    }

    public function testGetMonthAndYearForIdVsMissingData()
    {
        $mock = $this->mockRepositoryBuilder
            ->setMethods(['getIdToMonthMapKey'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getIdToMonthMapKey')
            ->with(self::TEST_ACCOUNT_ID)
            ->willReturn('key');

        $getMonthAndYearForId = new \ReflectionMethod(ExpenseRepository::class, 'getMonthAndYearForId');
        $getMonthAndYearForId->setAccessible(true);
        $result = $getMonthAndYearForId->invoke($mock, self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID);

        $this->assertNull($result);
    }

    public function testGetMonthAndYearForId()
    {
        $mock = $this->mockRepositoryBuilder
            ->setMethods(['getIdToMonthMapKey'])
            ->getMock();

        $mock->expects($this->once())
            ->method('getIdToMonthMapKey')
            ->with(self::TEST_ACCOUNT_ID)
            ->willReturn('key');

        $this->mockDataStore
            ->mapPutItem('key', self::TEST_EXPENSE_ID, '200012');

        $getMonthAndYearForId = new \ReflectionMethod(ExpenseRepository::class, 'getMonthAndYearForId');
        $getMonthAndYearForId->setAccessible(true);
        $result = $getMonthAndYearForId->invoke($mock, self::TEST_ACCOUNT_ID, self::TEST_EXPENSE_ID);

        $this->assertEquals([2000, 12], $result);
    }

    public function testGetYearAndMonthForExpense()
    {
        $mock = $this->mockRepositoryBuilder
            ->setMethodsExcept(['getYearAndMonthForExpense'])
            ->getMock();

        $getYearAndMonthForExpense = new \ReflectionMethod(ExpenseRepository::class, 'getYearAndMonthForExpense');
        $getYearAndMonthForExpense->setAccessible(true);
        $result = $getYearAndMonthForExpense->invoke($mock, $this->expense1);

        $this->assertEquals([$this->year, $this->month], $result);
    }

    public function testDeleteByIdVsNoItem()
    {
        $this->expectException(\Exception::class);
        $this->expenseRepository->deleteById(self::TEST_ACCOUNT_ID, 1);
    }

    public function testDeleteById()
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

    public function testGetForAccountAndMonthVsNoResults()
    {
        $fetched = $this->expenseRepository->getForAccountAndMonth(self::TEST_ACCOUNT_ID, $this->year, $this->month);
        $this->assertCount(0, $fetched);
    }

    public function testGetForAccountAndMonthVsResults()
    {
        // TODO
    }

    public function testGetMetaDataKey()
    {
        // TODO
    }

    public function testGetIdToMonthMapKey()
    {
        // TODO
    }

    public function testGetDataStoreKey()
    {
        // TODO
    }
}
