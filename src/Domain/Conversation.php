<?php

namespace MrBill\Domain;

use Exception;
use Generator;
use MrBill\Model\Expense;
use MrBill\Model\Message;
use MrBill\Model\Repository\ExpenseRepository;
use MrBill\Model\Repository\MessageRepository;
use MrBill\Model\Repository\TokenRepository;
use MrBill\Model\Token;
use MrBill\PhoneNumber;

/**
 * Represents the full conversation between Mr. Bill and one phone number
 */
class Conversation
{
    /** @var int */
    protected $accountId;

    /** @var PhoneNumber */
    protected $phone;

    /** @var DomainFactory */
    protected $domainFactory;

    /** @var MessageRepository */
    protected $messageRepository;

    public $totalMessages = 0;
    public $totalIncomingMessages = 0;

    public $totalHelpRequests = 0;

    public $totalExpenseMessages = 0;
    public $firstExpenseMessageTimestamp = 0;
    public $lastExpenseMessageTimestamp = 0;

    public function __construct(
        int $accountId,
        PhoneNumber $phone,
        DomainFactory $domainFactory,
        MessageRepository $messageRepository
    ) {
        $this->accountId = $accountId;
        $this->phone = $phone;
        $this->domainFactory = $domainFactory;
        $this->messageRepository = $messageRepository;

        foreach ($messageRepository->getAllMessagesForPhone($accountId, $phone) as $message) {
            $this->processOneMessage($message);
        }
    }

    /**
     * All messages need to be processed in order to build up the state of the Conversation
     * @param Message $message
     * @return MessageWithMeaning
     */
    protected function processOneMessage(Message $message) : MessageWithMeaning
    {
        $meaning = new MessageWithMeaning($message, $this->totalIncomingMessages);

        $this->totalMessages++;

        if ($meaning->isHelpRequest())
            $this->totalHelpRequests++;

        if ($message->isFromUser)
            $this->totalIncomingMessages++;

        if ($meaning->isExpenseMessage()) {
            $this->totalExpenseMessages++;

            // Assumes messages are ordered by time
            if (!$this->firstExpenseMessageTimestamp)
                $this->firstExpenseMessageTimestamp = $message->timestamp;
            $this->lastExpenseMessageTimestamp = $message->timestamp;
        }

        return $meaning;
    }

    public function getPhoneNumber() : PhoneNumber
    {
        return $this->phone;
    }

    /**
     * Add a message to the conversation (persists immediately).
     *
     * @param Message $message
     * @return MessageWithMeaning the inferred meaning of message
     * @throws Exception
     */
    public function addMessage(Message $message) : MessageWithMeaning
    {
        if ($this->accountId != $message->accountId || $this->phone != $message->phone)
            throw new Exception();

        $this->messageRepository->persistMessage($message);

        $messageWithMeaning = $this->processOneMessage($message);

        $this->persistExpenses($messageWithMeaning);

        return $messageWithMeaning;
    }

    protected function persistExpenses(MessageWithMeaning $messageWithMeaning)
    {
        if ($messageWithMeaning->isExpenseMessage()) {
            $parser = new ExpensesFromMessageParser();
            foreach ($parser->parse($messageWithMeaning->message) as $expense)
                /** @var Expense $expense */
                $this->domainFactory->getExpenseSet($this->phone)->addExpense($expense);
        }
    }

    public function removeAllData() : void
    {
        $this->messageRepository->removeAllMessagesForPhone($this->accountId, $this->phone);

        $this->totalExpenseMessages         =
        $this->totalIncomingMessages        =
        $this->totalHelpRequests            =
        $this->totalMessages                =
        $this->firstExpenseMessageTimestamp =
        $this->lastExpenseMessageTimestamp  = 0;
    }
}
