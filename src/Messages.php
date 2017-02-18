<?php

namespace MrBill;

use Generator;

class Messages
{
    public static function getHistoryForPhone($userPhone) : Generator
    {
        foreach (static::getAllMessages() as $message) {
            if ($message->userPhone == $userPhone) {
                yield $message;
            }
        }
    }

    public static function persistNewMessage(Message $message) : void
    {
        file_put_contents(static::getDataFileName(), $message->toJson() . "\n", FILE_APPEND);
    }

    protected static function getAllMessages() : Generator
    {
        $lines = explode("\n", file_get_contents(static::getDataFileName()));
        foreach ($lines as $line) {
            if ($line)
                yield Message::createFromJson($line);
        }
    }

    protected static function getDataFileName() : string
    {
        return '/var/www/messageHistory.db';
    }

    public static function removeAllMessageData() : void
    {
        file_put_contents(static::getDataFileName(), '');
    }
}
