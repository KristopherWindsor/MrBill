<?php

namespace MrBillTest\Unit\Domain;

use MrBill\Domain\Conversation;
use MrBill\Domain\DomainFactory;
use MrBill\Domain\ExpenseSet;
use MrBill\Model\Expense;
use MrBill\Model\Message;
use MrBill\Model\Repository\ExpenseRepository;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use MrBill\Persistence\MockDataStore;
use PHPUnit\Framework\TestCase;

class ConversationTest extends TestCase
{
    const TEST_PHONE = 14087226296;

    /** @var PhoneNumber */
    private $testPhone;

    /** @var MockDataStore */
    private $mockDataStore;

    /** @var Conversation */
    private $conversation;

    private $expenseSet;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        $this->mockDataStore = new MockDataStore();

        $repositoryFactory = new RepositoryFactory($this->mockDataStore);
        $domainFactory = new DomainFactoryChangeable($repositoryFactory);

        $domainFactory->expenseSets[self::TEST_PHONE] =
            new class($this->testPhone, $repositoryFactory->getExpenseRepository()) extends ExpenseSet {
                public $addedExpenses = [];

                public function __construct(PhoneNumber $phone, ExpenseRepository $expenseRepository) {
                }

                public function addExpense(Expense $expense)
                {
                    $this->addedExpenses[] = $expense;
                }
            };

        $this->conversation = $domainFactory->getConversation($this->testPhone);
        $this->expenseSet = $domainFactory->getExpenseSet($this->testPhone);
    }

    public function testGetPhoneNumber()
    {
        $this->assertEquals($this->testPhone, $this->conversation->getPhoneNumber());
    }

    public function testAddMessageAndCheckDataStore()
    {
        $time = 1488067264;
        $newMessage = new Message($this->testPhone, 'messageX', $time, true, 0);
        $expenseMessage = new Message($this->testPhone, '5 #h', $time, true, 0);

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
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), time(), true, 0);
        $outgoingMessage = new Message($this->testPhone, 'out', time(), false, 0);

        $this->assertEquals(0, $this->conversation->totalMessages);
        $this->conversation->addMessage($newMessage);
        $this->assertEquals(1, $this->conversation->totalMessages);
        $this->conversation->addMessage($outgoingMessage);
        $this->assertEquals(2, $this->conversation->totalMessages);
    }

    public function testAddMessageAndCountIncomingMessages()
    {
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), time(), true, 0);
        $outgoingMessage = new Message($this->testPhone, 'out', time(), false, 0);

        $this->conversation->addMessage($outgoingMessage);
        $this->assertEquals(0, $this->conversation->totalIncomingMessages);

        $this->conversation->addMessage($newMessage);
        $this->conversation->addMessage($newMessage);
        $this->assertEquals(2, $this->conversation->totalIncomingMessages);
    }

    public function testAddMessageAndCountHelpMessages()
    {
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), time(), true, 0);
        $helpMessage = new Message($this->testPhone, '?', time(), true, 0);

        $this->conversation->addMessage($newMessage);
        $this->assertEquals(0, $this->conversation->totalHelpRequests);

        $this->conversation->addMessage($helpMessage);
        $this->conversation->addMessage($helpMessage);
        $this->assertEquals(2, $this->conversation->totalHelpRequests);
    }

    public function testAddMessageAndTrackExpenseMessages()
    {
        $time = time();
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), $time, true, 0);
        $expenseMessage1 = new Message($this->testPhone, '5 #h', $time + 1, true, 0);
        $expenseMessage2 = new Message($this->testPhone, '5 #h', $time + 2, true, 0);

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

    public function testRemoveAllData()
    {
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), time(), true, 0);
        $expenseMessage = new Message($this->testPhone, '5.55 #tag', time(), true, 0);
        $outgoingMessage = new Message($this->testPhone, 'out', time(), false, 0);
        $helpMessage = new Message($this->testPhone, '?', time(), true, 0);

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
