<?php

namespace MrBillTest\Unit\Model;

use MrBill\Model\Expense;
use PHPUnit\Framework\TestCase;

class ExpenseTest extends TestCase
{
    const TEST_ID = 123;

    /** @var int */
    private $time;

    /** @var Expense */
    private $expense;

    public function setUp()
    {
        $this->time = time();

        $this->expense = new Expense(
            self::TEST_ID,
            $this->time,
            599,
            ['hash', 'tag'],
            'description',
            '1m',
            Expense::STATUS_FROM_MESSAGE,
            ['inf' => 'ok']
        );
    }

    public function testConstructor()
    {
        $this->assertEquals(self::TEST_ID, $this->expense->accountId);
        $this->assertEquals($this->time, $this->expense->timestamp);
        $this->assertEquals(599, $this->expense->amountInCents);
        $this->assertEquals(['hash', 'tag'], $this->expense->hashTags);
        $this->assertEquals('description', $this->expense->description);
        $this->assertEquals('1m', $this->expense->depreciation);
        $this->assertEquals(Expense::STATUS_FROM_MESSAGE, $this->expense->sourceType);
        $this->assertEquals(['inf' => 'ok'], $this->expense->sourceInfo);
    }

    public function testCreateFromMessage()
    {
        $this->expense = Expense::createFromMessage(
            self::TEST_ID,
            $this->time,
            599,
            ['h'],
            'des',
            ['inf']
        );

        $this->assertEquals(self::TEST_ID, $this->expense->accountId);
        $this->assertEquals($this->time, $this->expense->timestamp);
        $this->assertEquals(599, $this->expense->amountInCents);
        $this->assertEquals(['h'], $this->expense->hashTags);
        $this->assertEquals('des', $this->expense->description);
        $this->assertEquals(Expense::STATUS_FROM_MESSAGE, $this->expense->sourceType);
        $this->assertEquals(['inf'], $this->expense->sourceInfo);
    }

    public function testToMap()
    {
        $this->assertEquals(
            '{"accountId":' . self::TEST_ID . ',"timestamp":' . $this->time .
                ',"amountInCents":599,"hashTags":["hash","tag"],"description":"description","depreciation":"1m",' .
                '"sourceType":"_m","sourceInfo":{"inf":"ok"}}',
            json_encode($this->expense->toMap())
        );
    }

    public function testFromMap()
    {
        $loadedExpense = Expense::createFromMap($this->expense->toMap());

        $this->assertEquals($this->expense->accountId, $loadedExpense->accountId);
        $this->assertEquals($this->expense->timestamp, $loadedExpense->timestamp);
        $this->assertEquals($this->expense->amountInCents, $loadedExpense->amountInCents);
        $this->assertEquals($this->expense->hashTags, $loadedExpense->hashTags);
        $this->assertEquals($this->expense->description, $loadedExpense->description);
        $this->assertEquals($this->expense->depreciation, $loadedExpense->depreciation);
        $this->assertEquals($this->expense->sourceType, $loadedExpense->sourceType);
        $this->assertEquals($this->expense->sourceInfo, $loadedExpense->sourceInfo);
    }
}
