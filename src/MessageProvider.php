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

    public function getHistoryForPhone(int $userPhone) : Generator
    {
        foreach ($this->dataStore->get('messages' . $userPhone) as $messageInfo) {
            yield Message::createFromJson($messageInfo);
        }
    }

    public function persistNewMessage(Message $message) : void
    {
        $this->dataStore->append($this->getDataStoreKey($message->userPhone), $message->toJson());
    }

    public function removeAllMessageData(int $phone) : void
    {
        $this->dataStore->remove($this->getDataStoreKey($phone));
    }

    protected function getDataStoreKey(int $phone) : string
    {
        return 'messages' . $phone;
    }
}
