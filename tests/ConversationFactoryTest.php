<?php

namespace MrBill;

use MrBill\Persistence\DataStore;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // TODO move to bootstrap

class ConversationFactoryTest extends TestCase
{
    const TEST_PHONE = 14087226296;

    /** @var ConversationFactory */
    private $conversationFactory;

    /** @var PhoneNumber */
    private $testPhone;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        $this->conversationFactory = new ConversationFactory(new DataStore());
    }

    public function testGetConversation()
    {
        $conversation = $this->conversationFactory->getConversation($this->testPhone);
        $this->assertEquals($this->testPhone, $conversation->getPhoneNumber());
    }
}
