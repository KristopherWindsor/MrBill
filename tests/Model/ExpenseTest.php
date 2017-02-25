<?php

namespace MrBill\Model;

use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class ExpenseTest extends TestCase
{
    const TEST_PHONE = 14087226296;

    /** @var PhoneNumber */
    private $testPhone;

    /** @var int */
    private $time;

    /** @var Expense */
    private $expense;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        $this->time = time();

        $this->expense = new Expense(
            $this->testPhone,
            $this->time,
            599,
            ['hash', 'tag'],
            'description',
            Expense::SOURCE_TYPE_MESSAGE,
            sha1('x'),
            7
        );
    }

    public function testConstructor()
    {
        $this->assertEquals($this->testPhone, $this->expense->phone);
        $this->assertEquals($this->time, $this->expense->timestamp);
        $this->assertEquals(599, $this->expense->amountInCents);
        $this->assertEquals(['hash', 'tag'], $this->expense->hashTags);
        $this->assertEquals('description', $this->expense->description);
        $this->assertEquals(Expense::SOURCE_TYPE_MESSAGE, $this->expense->sourceType);
        $this->assertEquals(sha1('x'), $this->expense->sourceId);
        $this->assertEquals(7, $this->expense->entropy);
    }

    public function testCreateFromMessageWithEntropy()
    {
        $this->expense = Expense::createFromMessageWithEntropy(
            $this->testPhone,
            $this->time,
            599,
            ['h'],
            'des',
            'mid'
        );
        $expense2 = Expense::createFromMessageWithEntropy(
            $this->testPhone,
            $this->time,
            599,
            ['h'],
            'des',
            'mid'
        );

        $this->assertEquals($this->testPhone, $this->expense->phone);
        $this->assertEquals($this->time, $this->expense->timestamp);
        $this->assertEquals(599, $this->expense->amountInCents);
        $this->assertEquals(['h'], $this->expense->hashTags);
        $this->assertEquals('des', $this->expense->description);
        $this->assertEquals(Expense::SOURCE_TYPE_MESSAGE, $this->expense->sourceType);
        $this->assertEquals('mid', $this->expense->sourceId);

        $this->assertNotEquals($this->expense, $expense2);
    }

    public function testToJson()
    {
        $this->assertEquals(
            '{"phone":' . $this->testPhone . ',"timestamp":' . $this->time .
                ',"amountInCents":599,"hashTags":["hash","tag"],"description":"description","sourceType":"_m",' .
                '"sourceId":"11f6ad8ec52a2984abaafd7c3b516503785c2072","entropy":"7"}',
            $this->expense->toJson()
        );
    }

    public function testFromJson()
    {
        $loadedExpense = Expense::createFromJson($this->expense->toJson());

        $this->assertEquals($this->expense->phone, $loadedExpense->phone);
        $this->assertEquals($this->expense->timestamp, $loadedExpense->timestamp);
        $this->assertEquals($this->expense->amountInCents, $loadedExpense->amountInCents);
        $this->assertEquals($this->expense->hashTags, $loadedExpense->hashTags);
        $this->assertEquals($this->expense->description, $loadedExpense->description);
        $this->assertEquals($this->expense->sourceType, $loadedExpense->sourceType);
        $this->assertEquals($this->expense->sourceId, $loadedExpense->sourceId);
        $this->assertEquals($this->expense->entropy, $loadedExpense->entropy);
    }
}
