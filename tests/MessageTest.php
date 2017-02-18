<?php

namespace MrBill;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // TODO move to bootstrap

class MessageTest extends TestCase
{
    const TEST_PHONE = 14087226296;
    const TEST_TIMESTAMP = 1487403557;

    public function testIsHelp()
    {
        $scenarios = [
            [true, new Message(self::TEST_PHONE, '?', time(), true)],
            [true, new Message(self::TEST_PHONE, ' ? ', time(), true)],
            [false, new Message(self::TEST_PHONE, ' ? ', time(), false)],
            [false, new Message(self::TEST_PHONE, 'hello', time(), true)],
            [false, new Message(self::TEST_PHONE, 'hello', time(), false)],
        ];

        foreach ($scenarios as $index => [$expected, $message]) {
            $this->assertEquals($expected, $message->isHelpRequest(), 'Case ' . $index);
        }
    }

    public function testToJson()
    {
        $message = new Message(self::TEST_PHONE, 'some message', self::TEST_TIMESTAMP, true);

        $this->assertEquals('{"phone":' . self::TEST_PHONE . ',"message":"some message","timestamp":' . self::TEST_TIMESTAMP . ',"isFromUser":true}', $message->toJson());
    }

    public function testFromJson()
    {
        $message = new Message(self::TEST_PHONE, 'some message', self::TEST_TIMESTAMP, true);
        $loadedMessage = Message::createFromJson($message->toJson());

        $this->assertEquals(self::TEST_PHONE, $loadedMessage->userPhone);
        $this->assertEquals('some message', $loadedMessage->message);
        $this->assertEquals(self::TEST_TIMESTAMP, $loadedMessage->timestamp);
        $this->assertEquals(true, $loadedMessage->isFromUser);
    }
}
