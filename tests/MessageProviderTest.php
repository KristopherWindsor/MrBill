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

    /** @var PhoneNumber */
    private $testPhone;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        $this->messageProvider = new MessageProvider(new DataStore());
        $this->messageProvider->removeAllMessageData($this->testPhone);
    }

    public function testPersistAndGetMessage()
    {
        $newMessage = new Message($this->testPhone, 'message' . uniqid(), time(), true);
        $this->messageProvider->persistNewMessage($newMessage);

        $found = 0;
        foreach ($this->messageProvider->getHistoryForPhone($this->testPhone) as $loadedMessage) {
            $found++;
            $this->assertEquals($newMessage->message, $loadedMessage->message);
        }
        $this->assertEquals(1, $found);
    }
}
