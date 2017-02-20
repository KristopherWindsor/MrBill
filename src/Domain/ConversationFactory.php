<?php

namespace MrBill\Domain;

use MrBill\Model\Repository\MessageRepository;
use MrBill\PhoneNumber;

class ConversationFactory
{
    /** @var MessageRepository */
    protected $messageRepository;

    protected $conversations = [];

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function getConversation(PhoneNumber $phoneNumber) : Conversation
    {
        return $this->conversations[$phoneNumber->scalar] ??
            $this->conversations[$phoneNumber->scalar] = new Conversation($phoneNumber, $this->messageRepository);
    }
}
