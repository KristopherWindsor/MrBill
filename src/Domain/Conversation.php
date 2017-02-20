<?php

namespace MrBill\Domain;

use Exception;
use Generator;
use MrBill\Model\Message;
use MrBill\Model\Repository\MessageRepository;
use MrBill\PhoneNumber;

/**
 * Represents the full conversation between Mr. Bill and one phone number
 */
class Conversation
{
    /** @var PhoneNumber */
    protected $phone;

    /** @var MessageRepository */
    protected $messageRepository;

    public $totalHelpRequests = 0;
    public $totalMessages = 0;
    public $firstExpenseMessageTimestamp = 0;
    public $lastExpenseMessageTimestamp = 0;

    public function __construct(PhoneNumber $phone, MessageRepository $messageRepository)
    {
        $this->phone = $phone;
        $this->messageRepository = $messageRepository;

        foreach ($messageRepository->getAllMessagesForPhone($phone) as $message) {
            $this->processOneMessage($message);
        }
    }

    /**
     * All messages need to be processed in order to build up the state of the Conversation
     * @param Message $message
     */
    protected function processOneMessage(Message $message)
    {
        if ($this->totalMessages && $message->isHelpRequest())
            $this->totalHelpRequests++;
        $this->totalMessages++;

        if ($message->isFromUser && ExpenseRecord::getAllExpensesFromMessage($message->message)) {
            // Assumes messages are ordered by time
            if (!$this->firstExpenseMessageTimestamp)
                $this->firstExpenseMessageTimestamp = $message->timestamp;
            $this->lastExpenseMessageTimestamp = $message->timestamp;
        }
    }

    public function getPhoneNumber() : PhoneNumber
    {
        return $this->phone;
    }

    public function persistNewMessage(Message $message) : void
    {
        if ($this->phone != $message->phone)
            throw new Exception();

        $this->messageRepository->persistMessage($message);

        $this->processOneMessage($message);
    }

    public function removeAllMessageData() : void
    {
        $this->messageRepository->removeAllMessagesForPhone($this->phone);

        $this->totalHelpRequests            =
        $this->totalMessages                =
        $this->firstExpenseMessageTimestamp =
        $this->lastExpenseMessageTimestamp  = 0;
    }

    public function getAllExpenseRecords() : Generator
    {
        foreach ($this->messageRepository->getAllMessagesForPhone($this->phone) as $message) {
            /** @var Message $message */
            if ($message->isFromUser) {
                foreach (ExpenseRecord::getAllExpensesFromMessage($message->message) as $expenseRecord)
                    yield $expenseRecord;
            }
        }
    }
}
