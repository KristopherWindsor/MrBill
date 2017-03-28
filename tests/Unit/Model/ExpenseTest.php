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
            Expense::STATUS_FROM_MESSAGE,
            ['inf' => 'ok'],
            7
        );
    }

    public function testConstructor()
    {
        $this->assertEquals(self::TEST_ID, $this->expense->accountId);
        $this->assertEquals($this->time, $this->expense->timestamp);
        $this->assertEquals(599, $this->expense->amountInCents);
        $this->assertEquals(['hash', 'tag'], $this->expense->hashTags);
        $this->assertEquals('description', $this->expense->description);
        $this->assertEquals(Expense::STATUS_FROM_MESSAGE, $this->expense->sourceType);
        $this->assertEquals(['inf' => 'ok'], $this->expense->sourceInfo);
        $this->assertEquals(7, $this->expense->entropy);
    }

    public function testCreateFromMessageWithEntropy()
    {
        $this->expense = Expense::createFromMessageWithEntropy(
            self::TEST_ID,
            $this->time,
            599,
            ['h'],
            'des',
            ['inf']
        );
        $expense2 = Expense::createFromMessageWithEntropy(
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

        $this->assertNotEquals($this->expense, $expense2);
    }

    public function testToMap()
    {
        $this->assertEquals(
            '{"accountId":' . self::TEST_ID . ',"timestamp":' . $this->time .
                ',"amountInCents":599,"hashTags":["hash","tag"],"description":"description","sourceType":"_m",' .
                '"sourceInfo":{"inf":"ok"},"entropy":"7"}',
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
        $this->assertEquals($this->expense->sourceType, $loadedExpense->sourceType);
        $this->assertEquals($this->expense->sourceInfo, $loadedExpense->sourceInfo);
        $this->assertEquals($this->expense->entropy, $loadedExpense->entropy);
    }
}
