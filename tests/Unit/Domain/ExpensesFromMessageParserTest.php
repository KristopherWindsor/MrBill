<?php

namespace MrBillTest\Unit\Domain;

use MrBill\Domain\ExpensesFromMessageParser;
use MrBill\Model\Expense;
use MrBill\Model\Message;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class ExpensesFromMessageParserTest extends TestCase
{
    /** @var ExpensesFromMessageParser */
    private $parser;

    public function setUp()
    {
        $this->parser = new ExpensesFromMessageParser();
    }

    protected function callParseSingleLine($text)
    {
        $reflectionMethod = new \ReflectionMethod(ExpensesFromMessageParser::class, 'parseSingleLine');
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invoke($this->parser, $text);
    }

    /**
     * @dataProvider getParseSingleLineScenarios
     */
    public function testParseSingleLine($line, $expected)
    {
        $this->assertEquals($expected, $this->callParseSingleLine($line));
    }

    public function getParseSingleLineScenarios()
    {
        return
            [
                ['notParseable', []],
                ['not #parseable', []],
                ['5 no hashtags', []],
                ['5 #hash', [
                    'amount' => 5.00,
                    'hashtags' => ['hash'],
                    'description' => '#hash']],

                ['5.05 description #hash', [
                    'amount' => 5.05,
                    'hashtags' => ['hash'],
                    'description' => 'description #hash']],

                ['$55 #hash', [
                    'amount' => 55.00,
                    'hashtags' => ['hash'],
                    'description' => '#hash']],

                ['5 #multiple #hash', [
                    'amount' => 5.00,
                    'hashtags' => ['hash', 'multiple'],
                    'description' => '#multiple #hash']],

                [' 5 #multiple words #hash words ', [
                    'amount' => 5.00,
                    'hashtags' => ['hash', 'multiple'],
                    'description' => '#multiple words #hash words']],

                ['5.00 #c #b #a #b #f #b #e #b #d', [
                    'amount' => 5.00,
                    'hashtags' => ['a', 'b', 'c', 'd', 'e', 'f'],
                    'description' => '#c #b #a #b #f #b #e #b #d']],
            ];
    }

    public function testParse()
    {
        $phone = new PhoneNumber(14087226296);
        $time = 1234567890;

        $message = new Message($phone, " bad format \n 5 #h\n6 #t des \n\n ", $time, true, 0);

        $expenses = $this->parser->parse($message);
        $this->assertCount(2, $expenses);

        /** @var Expense $expense1 */
        $expense1 = $expenses[0];

        $this->assertEquals($phone,                       $expense1->phone);
        $this->assertEquals($time,                        $expense1->timestamp);
        $this->assertEquals(500,                          $expense1->amountInCents);
        $this->assertEquals(['h'],                        $expense1->hashTags);
        $this->assertEquals('#h',                         $expense1->description);
        $this->assertEquals(Expense::STATUS_FROM_MESSAGE, $expense1->sourceType);
        $this->assertEquals($message->toMap(),            $expense1->sourceInfo['message']);
    }
}
