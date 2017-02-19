<?php

namespace MrBill\Data;

use Generator;
use MrBill\Data\Conversation;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;

class ConversationFactory
{
    /** @var DataStore */
    protected $dataStore;

    public function __construct(DataStore $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    public function getConversation(PhoneNumber $phoneNumber) : Conversation
    {
        return new Conversation($phoneNumber, $this->dataStore);
    }
}
