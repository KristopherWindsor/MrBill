<?php

namespace MrBill;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // TODO move to bootstrap

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

    public function testIsHelp()
    {
        $scenarios = [
            [true, new Message($this->testPhone, '?', time(), true)],
            [true, new Message($this->testPhone, ' ? ', time(), true)],
            [false, new Message($this->testPhone, ' ? ', time(), false)],
            [false, new Message($this->testPhone, 'hello', time(), true)],
            [false, new Message($this->testPhone, 'hello', time(), false)],
        ];

        foreach ($scenarios as $index => [$expected, $message]) {
            $this->assertEquals($expected, $message->isHelpRequest(), 'Case ' . $index);
        }
    }

    public function testIsAnswer()
    {
        $scenarios = [
            [false, new Message($this->testPhone, ' y ', time(), false)],
            [true, new Message($this->testPhone, ' y ', time(), true)],
            [true, new Message($this->testPhone, 'no', time(), true)],
            [false, new Message($this->testPhone, 'sup', time(), true)],
        ];

        foreach ($scenarios as $index => [$expected, $message]) {
            $this->assertEquals($expected, $message->isAnswer(), 'Case ' . $index);
        }
    }

    public function testIsExpenseRecord()
    {
        $scenarios = [
            [false, new Message($this->testPhone, '8.50 #hash #tag', time(), false)],
            [false, new Message($this->testPhone, '9', time(), true)],
            [true, new Message($this->testPhone, '8.50 #hash #tag', time(), true)],
        ];

        foreach ($scenarios as $index => [$expected, $message]) {
            $this->assertEquals($expected, $message->isExpenseRecord(), 'Case ' . $index);
        }
    }

    public function testIsReportRequest()
    {
        $scenarios = [
            [false, new Message($this->testPhone, 'report', time(), false)],
            [true, new Message($this->testPhone, 'report', time(), true)],
            [false, new Message($this->testPhone, 'something', time(), true)],
        ];

        foreach ($scenarios as $index => [$expected, $message]) {
            $this->assertEquals($expected, $message->isReportRequest(), 'Case ' . $index);
        }
    }

    public function testToJson()
    {
        $message = new Message($this->testPhone, 'some message', self::TEST_TIMESTAMP, true);

        $this->assertEquals('{"phone":' . self::TEST_PHONE . ',"message":"some message","timestamp":' . self::TEST_TIMESTAMP . ',"isFromUser":true}', $message->toJson());
    }

    public function testFromJson()
    {
        $message = new Message($this->testPhone, 'some message', self::TEST_TIMESTAMP, true);
        $loadedMessage = Message::createFromJson($message->toJson());

        $this->assertEquals($this->testPhone, $loadedMessage->phone);
        $this->assertEquals('some message', $loadedMessage->message);
        $this->assertEquals(self::TEST_TIMESTAMP, $loadedMessage->timestamp);
        $this->assertEquals(true, $loadedMessage->isFromUser);
    }
}
