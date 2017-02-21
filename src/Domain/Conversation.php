<?php

namespace MrBill\Domain;

use Exception;
use Generator;
use MrBill\Model\Message;
use MrBill\Model\Repository\MessageRepository;
use MrBill\Model\Repository\TokenRepository;
use MrBill\Model\Token;
use MrBill\PhoneNumber;

/**
 * Represents the full conversation between Mr. Bill and one phone number
 */
class Conversation
{
    private const REPORT_ID = 1;

    /** @var PhoneNumber */
    protected $phone;

    /** @var MessageRepository */
    protected $messageRepository;

    /** @var TokenRepository */
    protected $tokenRepository;

    public $totalMessages = 0;
    public $totalIncomingMessages = 0;

    public $totalHelpRequests = 0;

    public $totalExpenseMessages = 0;
    public $firstExpenseMessageTimestamp = 0;
    public $lastExpenseMessageTimestamp = 0;

    public function __construct(
        PhoneNumber $phone,
        MessageRepository $messageRepository,
        TokenRepository $tokenRepository
    ) {
        $this->phone = $phone;
        $this->messageRepository = $messageRepository;
        $this->tokenRepository = $tokenRepository;

        foreach ($messageRepository->getAllMessagesForPhone($phone) as $message) {
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
    public function persistNewMessage(Message $message) : MessageWithMeaning
    {
        if ($this->phone != $message->phone)
            throw new Exception();

        $this->messageRepository->persistMessage($message);

        return $this->processOneMessage($message);
    }

    public function removeAllData() : void
    {
        $this->messageRepository->removeAllMessagesForPhone($this->phone);

        $this->totalExpenseMessages         =
        $this->totalIncomingMessages        =
        $this->totalHelpRequests            =
        $this->totalMessages                =
        $this->firstExpenseMessageTimestamp =
        $this->lastExpenseMessageTimestamp  = 0;

        $this->tokenRepository->deleteToken($this->phone, 1);
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

    public function getExistingReportToken() : ?Token
    {
        return $this->tokenRepository->getTokenIfExists($this->phone, self::REPORT_ID);
    }

    public function getOrCreateActiveReportToken() : Token
    {
        $existingToken = $this->tokenRepository->getTokenIfExists($this->phone, self::REPORT_ID);

        return
            $existingToken && !$existingToken->isExpired() ? $existingToken :

            $this->tokenRepository->persistToken(
                new Token(
                    $this->phone,
                    self::REPORT_ID,
                    dechex(random_int(pow(2, 48), pow(2, 52) - 1)),
                    time() + 3600 * 24 * 30
                )
            );
    }
}
