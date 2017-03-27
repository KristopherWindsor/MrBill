<?php

namespace MrBill\Model\Repository;

use Generator;
use MrBill\Model\Message;
use MrBill\PhoneNumber;

class MessageRepository extends Repository
{
    public function persistMessage(Message $message) : void
    {
        $key = $this->getDataStoreKey($message->accountId, $message->phone);

        $this->dataStore->listAddItem($key, json_encode($message->toMap()));
    }

    public function getAllMessagesForPhone(int $accountId, PhoneNumber $phoneNumber) : Generator
    {
        $key = $this->getDataStoreKey($accountId, $phoneNumber);

        foreach (array_reverse($this->dataStore->listGetAll($key)) as $item)
            yield Message::createFromMap(json_decode($item, true));
    }

    public function removeAllMessagesForPhone(int $accountId, PhoneNumber $phoneNumber) : void
    {
        $key = $this->getDataStoreKey($accountId, $phoneNumber);

        $this->dataStore->remove($key);
    }

    protected function getDataStoreKey(int $accountId, PhoneNumber $phone) : string
    {
        return 'messages:' . $accountId . ':' . $phone;
    }
}
