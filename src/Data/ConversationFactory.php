<?php

namespace MrBill\Data;

use MrBill\Model\Repository\MessageRepository;
use MrBill\PhoneNumber;

class ConversationFactory
{
    /** @var MessageRepository */
    protected $messageRepository;

    public function __construct(MessageRepository $messageRepository)
    {
        $this->messageRepository = $messageRepository;
    }

    public function getConversation(PhoneNumber $phoneNumber) : Conversation
    {
        return new Conversation($phoneNumber, $this->messageRepository);
    }
}
