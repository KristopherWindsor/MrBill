<?php

namespace MrBill;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // TODO move to bootstrap

class ExpenseRecordTest extends TestCase
{
    public function testValidAndInvalid()
    {
        $scenarios = [
            [true, '5 #hash'],
            [true, '5.05 description #hash'],
            [true, '$55 #hash'],
            [true, '5 #multiple #hash'],
            [true, '5 #multiple words #hash words'],
            [true, '  5    #hash   '],

            [false, '5.05 no hash tag'],
            [false, 'no #dollar #amount'],
            [false, '#dollar 5.05 #wrongplace'],
            [false, ''],
        ];

        foreach ($scenarios as $index => [$expected, $text]) {
            $this->assertEquals($expected, ExpenseRecord::getExpenseRecordIfValid($text) !== null, 'Case ' . $index);
        }
    }
}
