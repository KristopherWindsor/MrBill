<?php

namespace MrBill\Api;

use MrBill\Message;
use MrBill\MessageProvider;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php'; // TODO move to bootstrap

class V1Test extends TestCase
{
    const TEST_PHONE = 14087226296;
    const TEST_TIMESTAMP = 1487403557;

    /** @var PhoneNumber */
    private $testPhone;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        (new MessageProvider(new DataStore()))->removeAllMessageData($this->testPhone);
    }

    public function testInvalidRequest()
    {
        $v1 = new V1(new MessageProvider(new DataStore()), []);
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
        $v1 = new V1(new MessageProvider(new DataStore()), $request);
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Hello, I\'m Mr. Bill. Just let me know each time you spend $$, and I\'ll help you track expenses. Type "?" for help.</Message><Redirect>https://mrbill.kristopherwindsor.com/assets/mrbill.xml</Redirect></Response>',
            $v1->getResult()
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
        $v1 = new V1(new MessageProvider(new DataStore()), $request); // First one will be a welcome message

        $v1 = new V1(new MessageProvider(new DataStore()), $request);
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>1/4 Let\'s see how I can help you! Text "?" again to cycle through the help messages.</Message></Response>',
            $v1->getResult()
        );

        $v1 = new V1(new MessageProvider(new DataStore()), $request);
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>2/4 Every time you spend $$, send me a text like: 8.99 #eatout #lunch lunch with friends</Message></Response>',
            $v1->getResult()
        );
    }
}
