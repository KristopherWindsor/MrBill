<?php

namespace MrBill\Domain;

use MrBill\Model\Message;
use MrBill\Model\Repository\MessageRepository;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

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

        $this->conversation = (new ConversationFactory(new MessageRepository(new DataStore())))
            ->getConversation($this->testPhone);

        $this->conversation->removeAllMessageData();
    }

    public function testGetPhoneNumber()
    {
        $this->assertEquals($this->testPhone, $this->conversation->getPhoneNumber());
    }

    public function testPersistAndCountTotalMessages()
    {
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), time(), true, 0);
        $outgoingMessage = new Message($this->testPhone, 'out', time(), false, 0);

        $this->assertEquals(0, $this->conversation->totalMessages);
        $this->conversation->persistNewMessage($newMessage);
        $this->assertEquals(1, $this->conversation->totalMessages);
        $this->conversation->persistNewMessage($outgoingMessage);
        $this->assertEquals(2, $this->conversation->totalMessages);
    }

    public function testPersistAndCountIncomingMessages()
    {
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), time(), true, 0);
        $outgoingMessage = new Message($this->testPhone, 'out', time(), false, 0);

        $this->conversation->persistNewMessage($outgoingMessage);
        $this->assertEquals(0, $this->conversation->totalIncomingMessages);

        $this->conversation->persistNewMessage($newMessage);
        $this->conversation->persistNewMessage($newMessage);
        $this->assertEquals(2, $this->conversation->totalIncomingMessages);
    }

    public function testPersistAndCountHelpMessages()
    {
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), time(), true, 0);
        $helpMessage = new Message($this->testPhone, '?', time(), true, 0);

        $this->conversation->persistNewMessage($newMessage);
        $this->assertEquals(0, $this->conversation->totalHelpRequests);

        $this->conversation->persistNewMessage($helpMessage);
        $this->conversation->persistNewMessage($helpMessage);
        $this->assertEquals(2, $this->conversation->totalHelpRequests);
    }

    public function testPersistAndTrackExpenseMessages()
    {
        $time = time();
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), $time, true, 0);
        $expenseMessage1 = new Message($this->testPhone, '5 #h', $time + 1, true, 0);
        $expenseMessage2 = new Message($this->testPhone, '5 #h', $time + 2, true, 0);

        $this->conversation->persistNewMessage($newMessage);
        $this->assertEquals(0, $this->conversation->totalExpenseMessages);
        $this->assertEquals(0, $this->conversation->firstExpenseMessageTimestamp);
        $this->assertEquals(0, $this->conversation->lastExpenseMessageTimestamp);

        $this->conversation->persistNewMessage($expenseMessage1);
        $this->conversation->persistNewMessage($expenseMessage2);
        $this->assertEquals(2, $this->conversation->totalExpenseMessages);
        $this->assertEquals($time + 1, $this->conversation->firstExpenseMessageTimestamp);
        $this->assertEquals($time + 2, $this->conversation->lastExpenseMessageTimestamp);
    }
}
