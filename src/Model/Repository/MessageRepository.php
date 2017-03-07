<?php

namespace MrBill\Model\Repository;

use Generator;
use MrBill\Model\Message;
use MrBill\PhoneNumber;

class MessageRepository extends Repository
{
    public function persistMessage(Message $message) : void
    {
        $key = $this->getDataStoreKey($message->phone);

        $this->dataStore->listAddItem($key, json_encode($message->toMap()));
    }

    public function getAllMessagesForPhone(PhoneNumber $phoneNumber) : Generator
    {
        $key = $this->getDataStoreKey($phoneNumber);

        foreach (array_reverse($this->dataStore->listGetAll($key)) as $item)
            yield Message::createFromMap(json_decode($item, true));
    }

    public function removeAllMessagesForPhone(PhoneNumber $phoneNumber) : void
    {
        $key = $this->getDataStoreKey($phoneNumber);

        $this->dataStore->remove($key);
    }

    protected function getDataStoreKey(PhoneNumber $phone) : string
    {
        return 'messages:' . $phone;
    }
}
