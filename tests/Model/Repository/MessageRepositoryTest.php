<?php

namespace MrBillTest\Model\Repository;

use MrBill\Model\Message;
use MrBill\Model\Repository\MessageRepository;
use MrBill\Persistence\MockDataStore;
use MrBill\Model\Token;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class MessageRepositoryTest extends TestCase
{
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
                'messages14087226296' => [
                    '{"phone":14087226296,"message":"a message","timestamp":1488012941,"isFromUser":true,"entropy":0}',
                    '{"phone":14087226296,"message":"a message","timestamp":1488012941,"isFromUser":true,"entropy":0}'
                ]
            ],
            $this->mockDataStore->storage
        );
    }

    public function testPersistAndGetAll()
    {
        $phone = $this->message->phone;

        for ($i = 0; $i < 2; $i++)
            $this->messageRepository->persistMessage($this->message);

        $messages = iterator_to_array(
            $this->messageRepository->getAllMessagesForPhone($phone)
        );

        $this->assertCount(2, $messages);
        foreach ($messages as $message)
            $this->assertEquals($this->message, $message);
    }

    public function testPersistAndRemoveAll()
    {
        $phone = $this->message->phone;

        for ($i = 0; $i < 2; $i++)
            $this->messageRepository->persistMessage($this->message);

        $this->messageRepository->removeAllMessagesForPhone($phone);

        $this->assertEmpty($this->mockDataStore->storage);
    }
}
