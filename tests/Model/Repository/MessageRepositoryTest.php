<?php

namespace MrBill\Model\Repository;

use MrBill\Model\Message;
use MrBill\Model\Token;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class MessageRepositoryTest extends TestCase
{
    /** @var Message */
    private $message;

    /** @var MessageRepository */
    private $messageRepository;

    public function setUp()
    {
        $this->message = new Message(
            new PhoneNumber(14087226296),
            'a message',
            time(),
            true
        );

        $this->messageRepository = new MessageRepository(new DataStore());
    }

    public function testRemoveAllAndPersistAndGetAll()
    {
        $phone = $this->message->phone;

        $this->messageRepository->removeAllMessagesForPhone($phone);

        for ($i = 0; $i < 2; $i++)
            $this->messageRepository->persistMessage($this->message);

        $messages = iterator_to_array(
            $this->messageRepository->getAllMessagesForPhone($phone)
        );

        $this->assertCount(2, $messages);
        foreach ($messages as $message)
            $this->assertEquals($this->message, $message);
    }

    public function testPersistAndRemoveAllAndGetAll()
    {
        $phone = $this->message->phone;

        for ($i = 0; $i < 2; $i++)
            $this->messageRepository->persistMessage($this->message);

        $this->messageRepository->removeAllMessagesForPhone($phone);

        $messages = iterator_to_array(
            $this->messageRepository->getAllMessagesForPhone($phone)
        );

        $this->assertCount(0, $messages);
    }
}
