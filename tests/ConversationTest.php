<?php

namespace MrBill;

use MrBill\Persistence\DataStore;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // TODO move to bootstrap

class ConversationTest extends TestCase
{
    const TEST_PHONE = 14087226296;

    /** @var PhoneNumber */
    private $testPhone;

    /** @var Conversation */
    private $conversation;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        $this->conversation = (new ConversationFactory(new DataStore()))->getConversation($this->testPhone);

        $this->conversation->removeAllMessageData();
    }

    public function testGetPhoneNumber()
    {
        $this->assertEquals($this->testPhone, $this->conversation->getPhoneNumber());
    }

    public function testPersistAndCountMessages()
    {
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), time(), true);
        $helpMessage = new Message($this->testPhone, '?', time(), true);

        $this->assertEquals(0, $this->conversation->totalMessages);
        $this->conversation->persistNewMessage($newMessage);
        $this->conversation->persistNewMessage($newMessage);
        $this->assertEquals(2, $this->conversation->totalMessages);

        $this->assertEquals(0, $this->conversation->totalHelpRequests);
        $this->conversation->persistNewMessage($helpMessage);
        $this->conversation->persistNewMessage($helpMessage);
        $this->assertEquals(2, $this->conversation->totalHelpRequests);
        $this->assertEquals(4, $this->conversation->totalMessages);
    }
}
