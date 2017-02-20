<?php

namespace MrBill\Data;

use Exception;
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

        $this->totalHelpRequests = $this->totalMessages = 0;
    }
}
