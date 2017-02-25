<?php

namespace MrBill\Model;

use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    const TEST_PHONE = 14087226296;
    const TEST_TIMESTAMP = 1487403557;

    /** @var PhoneNumber */
    private $testPhone;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);
    }

    public function testCreateWithEntropy()
    {
        $time = time();
        $message1 = Message::createWithEntropy($this->testPhone, '?', $time, true);
        $message2 = Message::createWithEntropy($this->testPhone, '?', $time, true);

        $this->assertEquals($this->testPhone, $message1->phone);
        $this->assertEquals('?', $message1->message);
        $this->assertEquals($time, $message1->timestamp);
        $this->assertEquals(true, $message1->isFromUser);

        $this->assertNotEquals($message1, $message2);
    }

    public function testToMap()
    {
        $message = new Message($this->testPhone, 'some message', self::TEST_TIMESTAMP, true, 2);

        $this->assertEquals(
            '{"phone":' . self::TEST_PHONE . ',"message":"some message","timestamp":' . self::TEST_TIMESTAMP .
                ',"isFromUser":true,"entropy":2}',
            json_encode($message->toMap())
        );
    }

    public function testFromMap()
    {
        $message = new Message($this->testPhone, 'some message', self::TEST_TIMESTAMP, true, 0);
        $loadedMessage = Message::createFromMap($message->toMap());

        $this->assertEquals($this->testPhone, $loadedMessage->phone);
        $this->assertEquals('some message', $loadedMessage->message);
        $this->assertEquals(self::TEST_TIMESTAMP, $loadedMessage->timestamp);
        $this->assertEquals(true, $loadedMessage->isFromUser);
    }
}
