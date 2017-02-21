<?php

namespace MrBill\Domain;

use MrBill\Model\Message;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class MessageWithMeaningTest extends TestCase
{
    /**
     * @dataProvider meaningCalculationTestData
     *
     * @param string $messageText
     * @param bool $messageIsFromUser
     * @param bool $totalPrecedingIncomingMessages
     * @param string $meaningHint
     */
    public function testMeaningCalculation(
        string $messageText,
        bool $messageIsFromUser,
        bool $totalPrecedingIncomingMessages,
        string $meaningHint
    ) {
        $message = new Message(new PhoneNumber(14087226296), $messageText, time(), $messageIsFromUser, 0);
        $withMeaning = new MessageWithMeaning($message, $totalPrecedingIncomingMessages);

        $this->assertEquals($meaningHint == 'exp',  $withMeaning->isExpenseMessage());
        $this->assertEquals($meaningHint == '1st',  $withMeaning->isFirstMessage());
        $this->assertEquals($meaningHint == 'help', $withMeaning->isHelpRequest());
        $this->assertEquals($meaningHint == 'rr',   $withMeaning->isReportRequest());
        $this->assertEquals($meaningHint == '???',  $withMeaning->hasNoKnownMeaning());
        $this->assertEquals($meaningHint == 'misc', $withMeaning->isOutgoingMisc());
        $this->assertEquals($meaningHint == 'outQ', $withMeaning->isOutgoingQuestion());
    }

    public function meaningCalculationTestData() : array
    {
        return [
            ['5 #h',     true,  7, 'exp'],
            ['?',        true,  0, '1st'],
            ['?',        true,  7, 'help'],
            ['report',   true,  7, 'rr'],
            ['garbage',  true,  7, '???'],
            ['whatever', false, 7, 'misc'],
        ];
    }
}