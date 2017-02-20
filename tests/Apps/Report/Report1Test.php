<?php

namespace MrBill\Apps\Api;

use MrBill\Apps\Report\Report1;
use MrBill\Domain\Conversation;
use MrBill\Domain\ConversationFactory;
use MrBill\Model\Message;
use MrBill\Model\Repository\MessageRepository;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class Report1Test extends TestCase
{
    /** @var PhoneNumber */
    private $phone;

    /** @var MessageRepository */
    private $messageRepository;

    /** @var ConversationFactory */
    private $conversationFactory;

    /** @var Conversation */
    private $conversation;

    /** @var Report1 */
    private $report1;

    public function setUp()
    {
        $this->phone = new PhoneNumber(14087226296);

        $this->messageRepository = new MessageRepository(new DataStore());

        $this->conversationFactory = new ConversationFactory($this->messageRepository);

        $this->conversation = $this->conversationFactory->getConversation($this->phone);

        $this->conversation->removeAllMessageData();

        $this->report1 = new Report1(
            $this->conversationFactory,
            ['phone' => $this->phone->scalar]
        );
    }

    public function testGetDateText()
    {
        $time = 1234567890;

        $this->assertEquals('Jan 1st, 1970 &mdash; Jan 1st, 1970', $this->report1->getDateText());

        $this->conversation->persistNewMessage(new Message($this->phone, '5 #h', $time, true));
        $this->assertEquals('Feb 14th, 2009 &mdash; Feb 14th, 2009', $this->report1->getDateText());

        $this->conversation->persistNewMessage(new Message($this->phone, '5 #h', $time + 3600*24, true));
        $this->assertEquals('Feb 14th, 2009 &mdash; Feb 15th, 2009', $this->report1->getDateText());
    }

    public function testGetTableContents()
    {
        $this->assertEquals('', $this->report1->getTableContents());

        foreach (['5 #gas', '7 #food #eatout', '1.50 #gas', '2 #food'] as $message)
            $this->conversation->persistNewMessage(new Message($this->phone, $message, time(), true));

        $expected = <<<HTML
<tr><td>#eatout#food</td><td>7</td>
<tr><td>#food</td><td>2</td>
<tr><td>#gas</td><td>6.5</td>

HTML;

        $this->assertEquals($expected, $this->report1->getTableContents());
    }
}
