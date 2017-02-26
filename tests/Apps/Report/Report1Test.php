<?php

namespace MrBillTest\Apps\Report;

use MrBill\Apps\Report\Report1;
use MrBill\Domain\Conversation;
use MrBill\Domain\DomainFactory;
use MrBill\Model\Message;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Model\Token;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use MrBillTest\Model\Repository\MockDataStore;
use PHPUnit\Framework\TestCase;

class Report1Test extends TestCase
{
    /** @var PhoneNumber */
    private $phone;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /** @var DomainFactory */
    private $domainFactory;

    /** @var Conversation */
    private $conversation;

    /** @var Report1 */
    private $report1;

    public function setUp()
    {
        $this->phone = new PhoneNumber(14087226296);

        $this->repositoryFactory = new RepositoryFactory(new MockDataStore());

        $this->domainFactory = new DomainFactory($this->repositoryFactory);

        $this->conversation = $this->domainFactory->getConversation($this->phone);

        $this->conversation->removeAllData();
        $this->conversation->addMessage(new Message($this->phone, 'hi', time(), true, 0));

        $this->report1 = new Report1(
            $this->domainFactory,
            ['p' => $this->phone->scalar, 's' => $this->conversation->getOrCreateActiveReportToken()->secret]
        );
    }

    public function testInvalidRequestBadSecret()
    {
        $report = new Report1($this->domainFactory, ['p' => $this->phone->scalar, 's' => 'bad']);
        $this->assertTrue($report->hasInitializationError());
    }

    public function testInvalidRequestExpiredToken()
    {
        $this->repositoryFactory->getTokenRepository()->persistToken(
            new Token($this->phone, 1, 'mysecret', time() - 1)
        );

        $report = new Report1($this->domainFactory, ['p' => $this->phone->scalar, 's' => 'mysecret']);
        $this->assertTrue($report->hasInitializationError());
    }

    public function testGetDateText()
    {
        $time = 1234567890;

        $this->assertEquals('Jan 1st, 1970 &mdash; Jan 1st, 1970', $this->report1->getDateText());

        $this->conversation->addMessage(new Message($this->phone, '5 #h', $time, true, 0));
        $this->assertEquals('Feb 14th, 2009 &mdash; Feb 14th, 2009', $this->report1->getDateText());

        $this->conversation->addMessage(new Message($this->phone, '5 #h', $time + 3600*24, true, 0));
        $this->assertEquals('Feb 14th, 2009 &mdash; Feb 15th, 2009', $this->report1->getDateText());
    }

    public function testGetTableContents()
    {
        $this->assertEquals('', $this->report1->getTableContents());

        foreach (['5 #gas', '7 #food #eatout', '1.50 #gas', '2 #food'] as $message)
            $this->conversation->addMessage(new Message($this->phone, $message, time(), true, 0));

        $expected = <<<HTML
<tr><td>#eatout#food</td><td>7</td>
<tr><td>#food</td><td>2</td>
<tr><td>#gas</td><td>6.5</td>

HTML;

        $this->assertEquals($expected, $this->report1->getTableContents());
    }
}
