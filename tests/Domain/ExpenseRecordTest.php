<?php

namespace MrBill\Domain;

use PHPUnit\Framework\TestCase;

class ExpenseRecordTest extends TestCase
{
    public function testConstructorHashtagCalculation()
    {
        $message = '5.00 #c #b #a #b #f #b #e #b #d';
        $expenseRecord = new ExpenseRecord($message);
        $this->assertEquals(['a', 'b', 'c', 'd', 'e', 'f'], $expenseRecord->hashtags);
    }

    public function testGetAllExpensesFromMessage()
    {
        $scenarios = [
            [1, '5 #hash'],
            [1, '5.05 description #hash'],
            [1, '$55 #hash'],
            [1, '5 #multiple #hash'],
            [1, '5 #multiple words #hash words'],
            [1, '  5    #hash   '],

            [2, "5 #hash\n6 #hash"],
            [2, "not parseable\n5 #hash\n6 #hash\n\n\n"],

            [0, '5.05 no hash tag'],
            [0, 'no #dollar #amount'],
            [0, '#dollar 5.05 #wrongplace'],
            [0, ''],
        ];

        foreach ($scenarios as $index => list($expectedCount, $text)) {
            $this->assertCount(
                $expectedCount,
                ExpenseRecord::getAllExpensesFromMessage($text),
                'Case ' . $index
            );
        }
    }

    public function testGetHashtagsCanonical()
    {
        $scenarios = [
            ['5 #cat', '#cat'],
            ['5 #cat #dog', '#cat#dog'],
            ['5 #dog #cat', '#cat#dog'],
        ];

        foreach ($scenarios as $index => list($message, $canonical)) {
            $expenseRecord = new ExpenseRecord($message);
            $this->assertEquals($canonical, $expenseRecord->getHashtagsCanonical(), 'Case ' . $index);
        }
    }
}
