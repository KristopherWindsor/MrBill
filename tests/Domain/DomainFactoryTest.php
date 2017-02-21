<?php

namespace MrBill\Domain;

use MrBill\Model\Repository\MessageRepository;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class DomainFactoryTest extends TestCase
{
    const TEST_PHONE = 14087226296;

    /** @var DomainFactory */
    private $domainFactory;

    /** @var PhoneNumber */
    private $testPhone;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        $this->domainFactory = new DomainFactory(new RepositoryFactory(new DataStore()));
    }

    public function testGetConversation()
    {
        $conversation = $this->domainFactory->getConversation($this->testPhone);
        $this->assertEquals($this->testPhone, $conversation->getPhoneNumber());
    }
}
