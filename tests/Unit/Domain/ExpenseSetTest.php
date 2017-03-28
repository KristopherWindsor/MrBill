<?php

namespace MrBillTest\Unit\Domain;

use MrBill\Domain\ExpenseSet;
use MrBill\Model\Expense;
use MrBill\Model\Repository\ExpenseRepository;
use MrBill\PhoneNumber;
use MrBill\Persistence\MockDataStore;
use PHPUnit\Framework\TestCase;

class ExpenseSetTest extends TestCase
{
    const TEST_ID = 123;

    /** @var MockDataStore */
    private $mockDataStore;

    /** @var ExpenseSet */
    private $expenseSet;

    /** @var Expense */
    private $expense1;

    /** @var Expense */
    private $expense2;

    /** @var Expense */
    private $expense3;

    public function setUp()
    {
        $this->mockDataStore = new MockDataStore();

        $this->expenseSet =
            new class(self::TEST_ID, new ExpenseRepository($this->mockDataStore)) extends ExpenseSet {
                public function getAllMonthsWithExpensesHelper(?array $rangeData) : array {
                    return parent::getAllMonthsWithExpensesHelper($rangeData);
                }
            };

        $date = new \DateTime('2017-04-10');
        $this->expense1 = Expense::createFromMessageWithEntropy(
            self::TEST_ID,
            $date->getTimestamp(),
            100,
            ['h'],
            '',
            []
        );
        $this->expense2 = Expense::createFromMessageWithEntropy(
            self::TEST_ID,
            $date->modify('+10 months')->getTimestamp(),
            100,
            ['h'],
            '',
            []
        );
        $this->expense3 = Expense::createFromMessageWithEntropy(
            self::TEST_ID,
            $date->modify('+12 months')->getTimestamp(),
            100,
            ['h'],
            '',
            []
        );
    }

    public function testGetAllMonthsWithExpensesNoData()
    {
        $this->assertEquals([], $this->expenseSet->getAllMonthsWithExpenses());
    }

    public function testGetAllMonthsWithExpensesOneMonth()
    {
        $this->expenseSet->addExpense($this->expense1);

        $this->assertEquals(
            [[2017, 4]],
            $this->expenseSet->getAllMonthsWithExpenses()
        );
    }

    public function testGetAllMonthsWithExpensesMultipleMonths()
    {
        $this->expenseSet->addExpense($this->expense1);
        $this->expenseSet->addExpense($this->expense2);

        $this->assertEquals(
            [
                [2017, 4],
                [2017, 5],
                [2017, 6],
                [2017, 7],
                [2017, 8],
                [2017, 9],
                [2017, 10],
                [2017, 11],
                [2017, 12],
                [2018, 1],
                [2018, 2],
            ],
            $this->expenseSet->getAllMonthsWithExpenses()
        );
    }

    public function testGetAllMonthsWithExpensesThreeYears() : array
    {
        $this->expenseSet->addExpense($this->expense1);
        $this->expenseSet->addExpense($this->expense2);
        $this->expenseSet->addExpense($this->expense3);

        $allMonthsWithExpenses = $this->expenseSet->getAllMonthsWithExpenses();

        $this->assertEquals(
            [
                [2017, 4],  [2017, 5],  [2017, 6],
                [2017, 7],  [2017, 8],  [2017, 9],
                [2017, 10], [2017, 11], [2017, 12],
                [2018, 1],  [2018, 2],  [2018, 3],
                [2018, 4],  [2018, 5],  [2018, 6],
                [2018, 7],  [2018, 8],  [2018, 9],
                [2018, 10], [2018, 11], [2018, 12],
                [2019, 1],  [2019, 2],
            ],
            $allMonthsWithExpenses
        );

        return $allMonthsWithExpenses;
    }

    /**
     * @depends testGetAllMonthsWithExpensesThreeYears
     * @param array $allMonthsWithExpenses
     */
    public function testGetAllMonthsWithExpensesMonthsEnteredOutOfOrder(array $allMonthsWithExpenses)
    {
        $this->expenseSet->addExpense($this->expense3);
        $this->expenseSet->addExpense($this->expense1);
        $this->expenseSet->addExpense($this->expense2);

        $this->assertEquals($allMonthsWithExpenses, $this->expenseSet->getAllMonthsWithExpenses());
    }

    public function testGetAllExpenses()
    {
        $this->expenseSet->addExpense($this->expense1);
        $this->expenseSet->addExpense($this->expense2);
        $this->expenseSet->addExpense($this->expense3);

        $results = [];
        foreach ($this->expenseSet->getAllExpenses() as list($id, $expense))
            $results[$id] = $expense;

        $this->assertEquals($this->expense1, $results[1]);
        $this->assertEquals($this->expense2, $results[2]);
        $this->assertEquals($this->expense3, $results[3]);
    }

    public function testGetExpensesForMonthWhenDataStoreGivesUnorderedResults()
    {
        for ($i = 0; $i < 4; $i++)
            $this->expenseSet->addExpense($this->expense1);

        $expensesForMonth = $this->mockDataStore->storage['expenses:123:2017:04'];
        $this->mockDataStore->storage['expenses:123:2017:04'] = [
            2 => $expensesForMonth[2],
            1 => $expensesForMonth[1],
            4 => $expensesForMonth[4],
            3 => $expensesForMonth[3],
        ];

        $fromDomain = $this->expenseSet->getExpensesForMonth(2017, 4);
        $this->assertEquals([1, 2, 3, 4], array_keys($fromDomain));
    }
}
