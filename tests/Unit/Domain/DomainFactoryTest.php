<?php

namespace MrBillTest\Unit\Domain;

use MrBill\Domain\DomainFactory;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\PhoneNumber;
use MrBill\Persistence\MockDataStore;
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

        $this->domainFactory = new DomainFactory(new RepositoryFactory(new MockDataStore()));
    }

    public function testGetConversation()
    {
        $conversation = $this->domainFactory->getConversation($this->testPhone);
        $this->assertEquals($this->testPhone, $conversation->getPhoneNumber());
    }

    public function testGetExpenseSet()
    {
        $expenseSet = $this->domainFactory->getExpenseSet($this->testPhone);
        $this->assertEquals($this->testPhone, $expenseSet->getPhoneNumber());
    }

    public function testGetTokenSet()
    {
        $tokenSet1 = $this->domainFactory->getTokenSet($this->testPhone);
        $tokenSet2 = $this->domainFactory->getTokenSet($this->testPhone);
        $this->assertTrue($tokenSet1 === $tokenSet2);
    }
}
