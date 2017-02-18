<?php

namespace MrBill;

use Generator;

class MessageProvider
{
    public function getHistoryForPhone($userPhone) : Generator
    {
        foreach ($this->getAllMessages() as $message) {
            if ($message->userPhone == $userPhone) {
                yield $message;
            }
        }
    }

    public function persistNewMessage(Message $message) : void
    {
        file_put_contents($this->getDataFileName(), $message->toJson() . "\n", FILE_APPEND);
    }

    protected function getAllMessages() : Generator
    {
        $lines = explode("\n", file_get_contents($this->getDataFileName()));
        foreach ($lines as $line) {
            if ($line)
                yield Message::createFromJson($line);
        }
    }

    protected function getDataFileName() : string
    {
        return '/var/www/messageHistory.db';
    }

    public function removeAllMessageData() : void
    {
        file_put_contents($this->getDataFileName(), '');
    }
}
