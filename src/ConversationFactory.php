<?php

namespace MrBill;

use Generator;
use MrBill\Persistence\DataStore;

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
