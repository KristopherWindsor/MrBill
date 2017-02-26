<?php

namespace MrBillTest\Domain;

use MrBill\Domain\DomainFactory;
use MrBill\Domain\ExpenseSet;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\PhoneNumber;
use MrBillTest\Model\Repository\MockDataStore;
use PHPUnit\Framework\TestCase;

class ExpenseSetTest extends TestCase
{
    const TEST_PHONE = 14087226296;

    /** @var PhoneNumber */
    private $testPhone;

    /** @var MockDataStore */
    private $mockDataStore;

    /** @var ExpenseSet */
    private $expenseSet;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        $this->mockDataStore = new MockDataStore();

        $repositoryFactory = new RepositoryFactory($this->mockDataStore);

        $this->expenseSet = (new DomainFactory($repositoryFactory))
            ->getExpenseSet($this->testPhone);
    }

    public function testGetPhoneNumber()
    {
        $this->assertEquals($this->testPhone, $this->expenseSet->getPhoneNumber());
    }
}
