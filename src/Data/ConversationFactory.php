<?php

namespace MrBill\Data;

use Generator;
use MrBill\Data\Conversation;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use MrBill\Model\Repository\Repository;

class ConversationFactory extends Repository
{
    public function getConversation(PhoneNumber $phoneNumber) : Conversation
    {
        return new Conversation($phoneNumber, $this->dataStore);
    }
}
