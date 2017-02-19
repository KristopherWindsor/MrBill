<?php

namespace MrBill;

use Generator;
use MrBill\Persistence\DataStore;

class MessageProvider
{
    /** @var DataStore */
    protected $dataStore;

    public function __construct(DataStore $dataStore)
    {
        $this->dataStore = $dataStore;
    }

    public function getHistoryForPhone(PhoneNumber $phone) : Generator
    {
        foreach ($this->dataStore->get('messages' . $phone) as $messageInfo) {
            yield Message::createFromJson($messageInfo);
        }
    }

    public function persistNewMessage(Message $message) : void
    {
        $this->dataStore->append($this->getDataStoreKey($message->phone), $message->toJson());
    }

    public function removeAllMessageData(PhoneNumber $phone) : void
    {
        $this->dataStore->remove($this->getDataStoreKey($phone));
    }

    protected function getDataStoreKey(PhoneNumber $phone) : string
    {
        return 'messages' . $phone;
    }
}
