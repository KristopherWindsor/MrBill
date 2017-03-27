<?php

namespace MrBillTest\Unit\Model\Repository;

use MrBill\Model\Message;
use MrBill\Model\Repository\MessageRepository;
use MrBill\Persistence\MockDataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class MessageRepositoryTest extends TestCase
{
    const TEST_ACCOUNT_ID = 123;
    const TEST_TIME = 1488012941;

    /** @var Message */
    private $message;

    /** @var MockDataStore */
    private $mockDataStore;

    /** @var MessageRepository */
    private $messageRepository;

    public function setUp()
    {
        $this->message = new Message(
            self::TEST_ACCOUNT_ID,
            new PhoneNumber(14087226296),
            'a message',
            self::TEST_TIME,
            true,
            0
        );

        $this->mockDataStore = new MockDataStore();

        $this->messageRepository = new MessageRepository($this->mockDataStore);
    }

    public function testPersist()
    {
        for ($i = 0; $i < 2; $i++)
            $this->messageRepository->persistMessage($this->message);

        $this->assertEquals(
            [
                'messages:123:14087226296' => [
                    '{"accountId":123,"phone":14087226296,"message":"a message","timestamp":1488012941,"isFromUser":true,"entropy":0}',
                    '{"accountId":123,"phone":14087226296,"message":"a message","timestamp":1488012941,"isFromUser":true,"entropy":0}'
                ]
            ],
            $this->mockDataStore->storage
        );
    }

    public function testPersistAndGetAll()
    {
        $phone = $this->message->phone;
        $message2 = new Message(
            self::TEST_ACCOUNT_ID,
            $phone,
            'another message',
            self::TEST_TIME,
            true,
            0
        );

        $this->messageRepository->persistMessage($this->message);
        $this->messageRepository->persistMessage($message2);

        $messages = iterator_to_array(
            $this->messageRepository->getAllMessagesForPhone(self::TEST_ACCOUNT_ID, $phone)
        );

        $this->assertCount(2, $messages);
        $this->assertEquals($this->message, $messages[0]);
        $this->assertEquals($message2, $messages[1]);
    }

    public function testPersistAndRemoveAll()
    {
        $phone = $this->message->phone;

        for ($i = 0; $i < 2; $i++)
            $this->messageRepository->persistMessage($this->message);

        $this->messageRepository->removeAllMessagesForPhone(self::TEST_ACCOUNT_ID, $phone);

        $this->assertEmpty($this->mockDataStore->storage);
    }
}
