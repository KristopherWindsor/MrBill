<?php

namespace MrBillTest\Unit\Domain;

use MrBill\Domain\Account;
use MrBill\Model\Account as AccountModel;
use MrBill\Model\Repository\AccountRepository;
use MrBill\Persistence\MockDataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    const TEST_ID = 123;
    const TEST_PHONE = 14087226296;

    /** @var MockDataStore */
    private $mockDataStore;

    /** @var AccountRepository */
    private $accountRepository;

    /** @var AccountModel */
    private $accountModel;

    /** @var Account */
    private $account;

    public function setUp()
    {
        $this->mockDataStore = new MockDataStore();
        $this->accountRepository = new AccountRepository($this->mockDataStore);

        $this->accountModel = new AccountModel(self::TEST_ID, [new PhoneNumber(self::TEST_PHONE)]);
        $this->account = new Account($this->accountModel, $this->accountRepository);
    }

    public function testConstructor()
    {
        $property = new \ReflectionProperty(Account::class, 'account');
        $property->setAccessible(true);
        $this->assertEquals($this->accountModel, $property->getValue($this->account));

        $property = new \ReflectionProperty(Account::class, 'accountRepository');
        $property->setAccessible(true);
        $this->assertEquals($this->accountRepository, $property->getValue($this->account));
    }

    public function testGetByIDDoesNotExist()
    {
        $accountRepository = $this->getMockBuilder(AccountRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAccountIfExists'])
            ->getMock();

        $accountRepository->expects($this->once())
            ->method('getAccountIfExists')
            ->willReturn(null);

        $result = Account::getByIDIfExists(self::TEST_ID, $accountRepository);
        $this->assertNull($result);
    }

    public function testGetByIDDoesExist()
    {
        $accountRepository = $this->getMockBuilder(AccountRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAccountIfExists'])
            ->getMock();

        $accountRepository->expects($this->once())
            ->method('getAccountIfExists')
            ->willReturn($this->accountModel);

        $result = Account::getByIDIfExists(self::TEST_ID, $accountRepository);
        $this->assertNotEmpty($result);
        $this->assertEquals(self::TEST_ID, $result->getByID());
    }

    public function testGetOrCreateForPhoneNumberFound()
    {
        $accountRepository = $this->getMockBuilder(AccountRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAccountByPhoneIfExists'])
            ->getMock();

        $accountRepository->expects($this->once())
            ->method('getAccountByPhoneIfExists')
            ->willReturn($this->accountModel);

        $result = Account::getOrCreateForPhoneNumber(new PhoneNumber(self::TEST_PHONE), $accountRepository);
        $this->assertNotEmpty($result);
        $this->assertEquals(self::TEST_ID, $result->getByID());
    }

    public function testGetOrCreateForPhoneNumberNotFound()
    {
        $accountRepository = $this->getMockBuilder(AccountRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getAccountByPhoneIfExists', 'createNewAccount', 'updateAccount'])
            ->getMock();

        $accountRepository->expects($this->once())
            ->method('getAccountByPhoneIfExists')
            ->willReturn(null);

        $accountModel = new AccountModel(self::TEST_ID, []);
        $accountRepository->expects($this->once())
            ->method('createNewAccount')
            ->willReturn($accountModel);

        $accountRepository->expects($this->once())
            ->method('updateAccount');

        $result = Account::getOrCreateForPhoneNumber(new PhoneNumber(self::TEST_PHONE), $accountRepository);
        $this->assertNotEmpty($result);
        $this->assertEquals(self::TEST_ID, $result->getByID());
        $this->assertEquals([new PhoneNumber(self::TEST_PHONE)], $accountModel->phones);
    }

    public function testGetByID()
    {
        $this->assertEquals(self::TEST_ID, $this->account->getByID());
    }
}
