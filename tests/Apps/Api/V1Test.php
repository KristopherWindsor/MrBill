<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class V1Test extends TestCase
{
    const TEST_PHONE = 14087226296;
    const TEST_TIMESTAMP = 1487403557;

    /** @var PhoneNumber */
    private $testPhone;

    /** @var DomainFactory */
    private $conversationFactory;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        $repositoryFactory = new RepositoryFactory(new DataStore());

        $this->conversationFactory = new DomainFactory($repositoryFactory);

        $this->conversationFactory->getConversation($this->testPhone)->removeAllData();
    }

    public function testInvalidRequest()
    {
        $v1 = new V1($this->conversationFactory, []);
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Something is wrong.</Message></Response>',
            $v1->getResult()
        );
    }

    public function testWelcomeMessage()
    {
        $request =
            [
                'MessageSid' => 'abc',
                'From' => '14087226296',
                'Body' => 'hello',
            ];
        $v1 = new V1($this->conversationFactory, $request);

        $expected =
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Hi, I\'m Mr. Bill. Just text me each time you ' .
                'spend $$, and I\'ll help you track expenses. That\'s right... you keep track of your expenses by ' .
                'texting them to me.</Message><Redirect>https://mrbill.kristopherwindsor.com/api/sleep.php?' .
                'sleep=6&amp;content=welcome2</Redirect></Response>';

        $this->assertEquals(
            $expected,
            $v1->getResult()
        );
    }

    public function testExpenseRecord()
    {
        $this->testWelcomeMessage(); // Get those out of the way

        $request =
            [
                'MessageSid' => 'abc',
                'From' => self::TEST_PHONE,
                'Body' => '$7 #tag',
            ];

        $expected = [
            1 => '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Got it. I\'ll send you a report once ' .
                'I\'ve got a few more expenses.</Message></Response>',
            5 => '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Keep them coming!</Message></Response>',
        ];

        for ($i = 1; $i < 7; $i++) {
            $v1 = new V1($this->conversationFactory, $request);
            $this->assertEquals(
                $expected[$i] ?? '<?xml version="1.0" encoding="UTF-8" ?><Response></Response>',
                $v1->getResult(),
                'Case ' . $i
            );
        }
    }

    public function testHelpRequest()
    {
        $this->testWelcomeMessage(); // Get those out of the way

        $request =
            [
                'MessageSid' => 'abc',
                'From' => '14087226296',
                'Body' => ' ?',
            ];

        $v1 = new V1($this->conversationFactory, $request);
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>1/5 Let\'s see how I can help you! Text "?" again to cycle through the help messages.</Message></Response>',
            $v1->getResult()
        );

        $v1 = new V1($this->conversationFactory, $request);
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>2/5 Every time you spend $$, send me a text like: 8.99 #eatout #lunch lunch with friends</Message></Response>',
            $v1->getResult()
        );
    }

    public function testCreateReportAndGetReply()
    {
        $this->testWelcomeMessage(); // Get those out of the way

        $request =
            [
                'MessageSid' => 'abc',
                'From' => '14087226296',
                'Body' => 'report',
            ];

        $v1 = new V1($this->conversationFactory, $request);

        $secret = $this->conversationFactory->getConversation($this->testPhone)->getExistingReportToken()->secret;

        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Your report! ' .
                'https://mrbill.kristopherwindsor.com/report/1?p=' . $this->testPhone . '&amp;s=' . $secret .
                '</Message></Response>',
            $v1->getResult()
        );
    }
}
