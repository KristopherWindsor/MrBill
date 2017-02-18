<?php

namespace MrBill\Api;

use MrBill\Message;
use MrBill\Messages;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php'; // TODO move to bootstrap

class V1Test extends TestCase
{
    const TEST_PHONE = 14087226296;
    const TEST_TIMESTAMP = 1487403557;

    public function setUp()
    {
        Messages::removeAllMessageData();
    }

    public function testInvalidRequest()
    {
        $v1 = new V1([]);
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Something is wrong.</Message></Response>',
            $v1->result
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
        $v1 = new V1($request);
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Hello, I\'m Mr. Bill. Just let me know each time you spend $$, and I\'ll help you track expenses. Type "?" for help.<Media>https://mrbill.kristopherwindsor.com/assets/mrbill.png</Media></Message></Response>',
            $v1->result
        );
    }

    public function testHelpRequest()
    {
        $request =
            [
                'MessageSid' => 'abc',
                'From' => '14087226296',
                'Body' => ' ?',
            ];
        $v1 = new V1($request); // First one will be a welcome message
        $v1 = new V1($request);
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Every time you spend $$, send me a text like: 8.99 #eatout #lunch lunch with friends</Message></Response>',
            $v1->result
        );
    }
}
