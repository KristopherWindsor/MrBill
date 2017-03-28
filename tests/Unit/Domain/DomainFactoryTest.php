<?php

namespace MrBillTest\Unit\Domain;

use MrBill\Domain\DomainFactory;
use MrBill\Model\Account as AccountModel;
use MrBill\Model\Repository\AccountRepository;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\PhoneNumber;
use MrBill\Persistence\MockDataStore;
use PHPUnit\Framework\TestCase;

class DomainFactoryTest extends TestCase
{
    const TEST_ID = 123;
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

    public function testGetAccountExists()
    {
        $mockAccountRepository = $this
            ->getMockBuilder(AccountRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAccountIfExists'])
            ->getMock();

        $accountModel = new AccountModel(123, []);
        $mockAccountRepository
            ->expects($this->once())
            ->method('getAccountIfExists')
            ->willReturn($accountModel);

        $repositoryFactory = new RepositoryFactory(new MockDataStore());
        $property = new \ReflectionProperty(RepositoryFactory::class, 'accountRepository');
        $property->setAccessible(true);
        $property->setValue($repositoryFactory, $mockAccountRepository);

        $domainFactory = new DomainFactory($repositoryFactory);

        $account1 = $domainFactory->getAccount(123);
        $account2 = $domainFactory->getAccount(123);
        $this->assertTrue($account1 === $account2);
    }

    public function testGetAccountDoesNotExist()
    {
        $mockAccountRepository = $this
            ->getMockBuilder(AccountRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAccountIfExists'])
            ->getMock();

        $mockAccountRepository
            ->expects($this->once())
            ->method('getAccountIfExists')
            ->willReturn(null);

        $repositoryFactory = new RepositoryFactory(new MockDataStore());
        $property = new \ReflectionProperty(RepositoryFactory::class, 'accountRepository');
        $property->setAccessible(true);
        $property->setValue($repositoryFactory, $mockAccountRepository);

        $domainFactory = new DomainFactory($repositoryFactory);

        $account = $domainFactory->getAccount(123);
        $this->assertNull($account);
    }

    public function testGetAccountByPhoneNumber()
    {
        $mockAccountRepository = $this
            ->getMockBuilder(AccountRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAccountByPhoneIfExists'])
            ->getMock();

        $accountModel = new AccountModel(123, []);
        $mockAccountRepository
            ->expects($this->exactly(2))
            ->method('getAccountByPhoneIfExists')
            ->willReturn($accountModel);

        $repositoryFactory = new RepositoryFactory(new MockDataStore());
        $property = new \ReflectionProperty(RepositoryFactory::class, 'accountRepository');
        $property->setAccessible(true);
        $property->setValue($repositoryFactory, $mockAccountRepository);

        $domainFactory = new DomainFactory($repositoryFactory);

        $phone = new PhoneNumber(self::TEST_PHONE);
        $account1 = $domainFactory->getAccountByPhoneNumber($phone);
        $account2 = $domainFactory->getAccountByPhoneNumber($phone);
        $this->assertTrue($account1 === $account2);
    }

    public function testGetConversation()
    {
        $conversation = $this->domainFactory->getConversation(123, $this->testPhone);
        $this->assertEquals($this->testPhone, $conversation->getPhoneNumber());
    }

    public function testGetExpenseSet()
    {
        $expenseSet1 = $this->domainFactory->getExpenseSet(self::TEST_ID);
        $expenseSet2 = $this->domainFactory->getExpenseSet(self::TEST_ID);
        $this->assertTrue($expenseSet1 === $expenseSet2);
    }

    public function testGetTokenSet()
    {
        $tokenSet1 = $this->domainFactory->getTokenSet(self::TEST_ID);
        $tokenSet2 = $this->domainFactory->getTokenSet(self::TEST_ID);
        $this->assertTrue($tokenSet1 === $tokenSet2);
    }
}
