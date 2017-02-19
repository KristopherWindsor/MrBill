<?php

namespace MrBill;

use MrBill\Persistence\DataStore;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // TODO move to bootstrap

class MessageProviderTest extends TestCase
{
    const TEST_PHONE = 14087226296;

    /** @var MessageProvider */
    private $messageProvider;

    public function setUp()
    {
        $this->messageProvider = new MessageProvider(new DataStore());
        $this->messageProvider->removeAllMessageData(self::TEST_PHONE);
    }

    public function testPersistAndGetMessage()
    {
        $newMessage = new Message(self::TEST_PHONE, 'message' . uniqid(), time(), true);
        $this->messageProvider->persistNewMessage($newMessage);

        $found = 0;
        foreach ($this->messageProvider->getHistoryForPhone(self::TEST_PHONE) as $loadedMessage) {
            $found++;
            $this->assertEquals($newMessage->message, $loadedMessage->message);
        }
        $this->assertEquals(1, $found);
    }
}
