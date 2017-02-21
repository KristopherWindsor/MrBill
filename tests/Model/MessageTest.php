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

    public function testIsHelp()
    {
        $scenarios = [
            [true, new Message($this->testPhone, '?', time(), true, 0)],
            [true, new Message($this->testPhone, ' ? ', time(), true, 0)],
            [false, new Message($this->testPhone, ' ? ', time(), false, 0)],
            [false, new Message($this->testPhone, 'hello', time(), true, 0)],
            [false, new Message($this->testPhone, 'hello', time(), false, 0)],
        ];

        foreach ($scenarios as $index => [$expected, $message]) {
            $this->assertEquals($expected, $message->isHelpRequest(), 'Case ' . $index);
        }
    }

    public function testIsAnswer()
    {
        $scenarios = [
            [false, new Message($this->testPhone, ' y ', time(), false, 0)],
            [true, new Message($this->testPhone, ' y ', time(), true, 0)],
            [true, new Message($this->testPhone, 'no', time(), true, 0)],
            [false, new Message($this->testPhone, 'sup', time(), true, 0)],
        ];

        foreach ($scenarios as $index => [$expected, $message]) {
            $this->assertEquals($expected, $message->isAnswer(), 'Case ' . $index);
        }
    }

    public function testIsReportRequest()
    {
        $scenarios = [
            [false, new Message($this->testPhone, 'report', time(), false, 0)],
            [true, new Message($this->testPhone, 'report', time(), true, 0)],
            [false, new Message($this->testPhone, 'something', time(), true, 0)],
        ];

        foreach ($scenarios as $index => [$expected, $message]) {
            $this->assertEquals($expected, $message->isReportRequest(), 'Case ' . $index);
        }
    }

    public function testToJson()
    {
        $message = new Message($this->testPhone, 'some message', self::TEST_TIMESTAMP, true, 2);

        $this->assertEquals(
            '{"phone":' . self::TEST_PHONE . ',"message":"some message","timestamp":' . self::TEST_TIMESTAMP .
                ',"isFromUser":true,"entropy":2}',
            $message->toJson()
        );
    }

    public function testFromJson()
    {
        $message = new Message($this->testPhone, 'some message', self::TEST_TIMESTAMP, true, 0);
        $loadedMessage = Message::createFromJson($message->toJson());

        $this->assertEquals($this->testPhone, $loadedMessage->phone);
        $this->assertEquals('some message', $loadedMessage->message);
        $this->assertEquals(self::TEST_TIMESTAMP, $loadedMessage->timestamp);
        $this->assertEquals(true, $loadedMessage->isFromUser);
    }
}
