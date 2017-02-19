<?php

namespace MrBill;

use Generator;
use Exception;
use MrBill\Persistence\DataStore;

/**
 * Represents the full conversation between Mr. Bill and one phone number
 */
class Conversation
{
    /** @var PhoneNumber */
    protected $phone;

    /** @var DataStore */
    protected $dataStore;

    public $totalHelpRequests = 0;
    public $totalMessages = 0;

    public function __construct(PhoneNumber $phone, DataStore $dataStore)
    {
        $this->phone = $phone;
        $this->dataStore = $dataStore;

        foreach ($dataStore->get($this->getDataStoreKey($phone)) as $item) {
            $this->processOneMessage(Message::createFromJson($item));
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

        $this->dataStore->append($this->getDataStoreKey($this->phone), $message->toJson());
        $this->processOneMessage($message);
    }

    public function removeAllMessageData() : void
    {
        $this->dataStore->remove($this->getDataStoreKey($this->phone));
        $this->totalHelpRequests = $this->totalMessages = 0;
    }

    protected function getDataStoreKey(PhoneNumber $phone) : string
    {
        return 'messages' . $phone;
    }
}
