<?php

namespace MrBillTest\Unit\Domain;

use MrBill\Domain\Conversation;
use MrBill\Domain\DomainFactory;
use MrBill\Domain\ExpenseSet;
use MrBill\Model\Expense;
use MrBill\Model\Message;
use MrBill\Model\Repository\ExpenseRepository;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\PhoneNumber;
use MrBill\Persistence\MockDataStore;
use PHPUnit\Framework\TestCase;

class ConversationTest extends TestCase
{
    const TEST_ID = 123;
    const TEST_PHONE = 14087226296;

    /** @var PhoneNumber */
    private $testPhone;

    /** @var MockDataStore */
    private $mockDataStore;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var DomainFactory */
    private $domainFactory;

    /** @var Conversation */
    private $conversation;

    /** @var object */
    private $expenseSet;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        $this->mockDataStore = new MockDataStore();
        $this->repositoryFactory = new RepositoryFactory($this->mockDataStore);
        $this->domainFactory = new DomainFactoryChangeable($this->repositoryFactory);

        $this->domainFactory->expenseSets[self::TEST_ID] =
            new class(self::TEST_ID, $this->repositoryFactory->getExpenseRepository()) extends ExpenseSet {
                public $addedExpenses = [];

                public function addExpense(Expense $expense) : int
                {
                    $this->addedExpenses[] = $expense;
                    return count($this->addedExpenses);
                }
            };

        $this->conversation = $this->domainFactory->getConversation(self::TEST_ID, $this->testPhone);
        $this->expenseSet = $this->domainFactory->getExpenseSet(self::TEST_ID);
    }

    public function testGetPhoneNumber()
    {
        $this->assertEquals($this->testPhone, $this->conversation->getPhoneNumber());
    }

    public function testAddMessageWrongPhone()
    {
        $newMessage = new Message(self::TEST_ID, new PhoneNumber(14081234567), 'messageX', time(), true, 0);
        $this->expectException(\Exception::class);
        $this->conversation->addMessage($newMessage);
    }

    public function testAddMessageAndCheckDataStore()
    {
        $time = 1488067264;
        $newMessage = new Message(self::TEST_ID, $this->testPhone, 'messageX', $time, true, 0);
        $expenseMessage = new Message(self::TEST_ID, $this->testPhone, '5 #h', $time, true, 0);

        $this->conversation->addMessage($newMessage);
        $this->conversation->addMessage($expenseMessage);

        $this->assertCount(1, $this->expenseSet->addedExpenses);
        /** @var Expense $addedExpense */
        $addedExpense = $this->expenseSet->addedExpenses[0];
        $this->assertEquals(500, $addedExpense->amountInCents);
        $this->assertEquals(['h'], $addedExpense->hashTags);
    }

    public function testAddMessageAndCountTotalMessages()
    {
        $newMessage = new Message(self::TEST_ID, $this->testPhone, 'message' . uniqid(), time(), true, 0);
        $outgoingMessage = new Message(self::TEST_ID, $this->testPhone, 'out', time(), false, 0);

        $this->assertEquals(0, $this->conversation->totalMessages);
        $this->conversation->addMessage($newMessage);
        $this->assertEquals(1, $this->conversation->totalMessages);
        $this->conversation->addMessage($outgoingMessage);
        $this->assertEquals(2, $this->conversation->totalMessages);
    }

    public function testAddMessageAndCountIncomingMessages()
    {
        $newMessage = new Message(self::TEST_ID, $this->testPhone, 'message' . uniqid(), time(), true, 0);
        $outgoingMessage = new Message(self::TEST_ID, $this->testPhone, 'out', time(), false, 0);

        $this->conversation->addMessage($outgoingMessage);
        $this->assertEquals(0, $this->conversation->totalIncomingMessages);

        $this->conversation->addMessage($newMessage);
        $this->conversation->addMessage($newMessage);
        $this->assertEquals(2, $this->conversation->totalIncomingMessages);
    }

    public function testAddMessageAndCountHelpMessages()
    {
        $newMessage = new Message(self::TEST_ID, $this->testPhone, 'message' . uniqid(), time(), true, 0);
        $helpMessage = new Message(self::TEST_ID, $this->testPhone, '?', time(), true, 0);

        $this->conversation->addMessage($newMessage);
        $this->assertEquals(0, $this->conversation->totalHelpRequests);

        $this->conversation->addMessage($helpMessage);
        $this->conversation->addMessage($helpMessage);
        $this->assertEquals(2, $this->conversation->totalHelpRequests);
    }

    public function testAddMessageAndTrackExpenseMessages()
    {
        $time = time();
        $newMessage = new Message(self::TEST_ID, $this->testPhone, 'message' . uniqid(), $time, true, 0);
        $expenseMessage1 = new Message(self::TEST_ID, $this->testPhone, '5 #h', $time + 1, true, 0);
        $expenseMessage2 = new Message(self::TEST_ID, $this->testPhone, '5 #h', $time + 2, true, 0);

        $this->conversation->addMessage($newMessage);
        $this->assertEquals(0, $this->conversation->totalExpenseMessages);
        $this->assertEquals(0, $this->conversation->firstExpenseMessageTimestamp);
        $this->assertEquals(0, $this->conversation->lastExpenseMessageTimestamp);

        $this->conversation->addMessage($expenseMessage1);
        $this->conversation->addMessage($expenseMessage2);
        $this->assertEquals(2, $this->conversation->totalExpenseMessages);
        $this->assertEquals($time + 1, $this->conversation->firstExpenseMessageTimestamp);
        $this->assertEquals($time + 2, $this->conversation->lastExpenseMessageTimestamp);
    }

    public function testLoadConversationWithExistingMessages()
    {
        $time = time();
        $newMessage = new Message(self::TEST_ID, $this->testPhone, 'messageX', $time, true, 0);
        $expenseMessage = new Message(self::TEST_ID, $this->testPhone, '5 #h', $time, true, 0);

        $this->conversation->addMessage($newMessage);
        $this->conversation->addMessage($expenseMessage);

        $conversation = new Conversation(
            self::TEST_ID,
            $this->testPhone,
            $this->domainFactory,
            $this->repositoryFactory->getMessageRepository()
        );
        $this->assertEquals($conversation->totalMessages, 2);
        $this->assertEquals($conversation->totalExpenseMessages, 1);
    }

    public function testRemoveAllData()
    {
        $newMessage = new Message(self::TEST_ID, $this->testPhone, 'message' . uniqid(), time(), true, 0);
        $expenseMessage = new Message(self::TEST_ID, $this->testPhone, '5.55 #tag', time(), true, 0);
        $outgoingMessage = new Message(self::TEST_ID, $this->testPhone, 'out', time(), false, 0);
        $helpMessage = new Message(self::TEST_ID, $this->testPhone, '?', time(), true, 0);

        $this->conversation->addMessage($newMessage);
        $this->conversation->addMessage($expenseMessage);
        $this->conversation->addMessage($outgoingMessage);
        $this->conversation->addMessage($helpMessage);

        $this->conversation->removeAllData();
        $this->assertEquals(0, $this->conversation->totalMessages);
        $this->assertEquals(0, $this->conversation->totalIncomingMessages);
        $this->assertEquals(0, $this->conversation->totalHelpRequests);
        $this->assertEquals(0, $this->conversation->totalExpenseMessages);
        $this->assertEquals(0, $this->conversation->firstExpenseMessageTimestamp);
        $this->assertEquals(0, $this->conversation->lastExpenseMessageTimestamp);
    }
}
