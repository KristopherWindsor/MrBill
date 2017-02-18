<?php

namespace MrBill;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // TODO move to bootstrap

class MessagesTest extends TestCase
{
    const TEST_PHONE = 14087226296;

    public function setUp()
    {
        Messages::removeAllMessageData();
    }

    public function testPersistAndGetMessage()
    {
        $newMessage = new Message(self::TEST_PHONE, 'message' . uniqid(), time(), true);
        Messages::persistNewMessage($newMessage);

        $found = 0;
        foreach (Messages::getHistoryForPhone(self::TEST_PHONE) as $loadedMessage) {
            $found++;
            $this->assertEquals($newMessage->message, $loadedMessage->message);
        }
        $this->assertEquals(1, $found);
    }
}
