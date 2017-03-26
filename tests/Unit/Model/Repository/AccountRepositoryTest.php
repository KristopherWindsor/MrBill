<?php

namespace MrBillTest\Unit\Model\Repository;

use MrBill\Model\Account;
use MrBill\Model\Repository\AccountRepository;
use MrBill\Persistence\MockDataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class AccountRepositoryTest extends TestCase
{
    const TEST_ID = 123;
    const TEST_PHONE1 = 14087226296;
    const TEST_PHONE2 = 14087226297;

    /** @var Account */
    private $account0;

    /** @var Account */
    private $account;

    /** @var MockDataStore */
    private $mockDataStore;

    /** @var object */
    private $accountRepository;

    public function setUp()
    {
        $this->account0 = new Account(self::TEST_ID, []);

        $this->account = new Account(
            self::TEST_ID,
            [new PhoneNumber(self::TEST_PHONE1), new PhoneNumber(self::TEST_PHONE2)]
        );

        $this->mockDataStore = new MockDataStore();

        $this->accountRepository = new class($this->mockDataStore) extends AccountRepository {
            public function storeAccount(Account $account): void
            {
                parent::storeAccount($account);
            }
            public function getRemovedPhones(Account $oldAccount, Account $newAccount): \Generator
            {
                return parent::getRemovedPhones($oldAccount, $newAccount);
            }
            public function getAddedPhones(Account $oldAccount, Account $newAccount): \Generator
            {
                return parent::getAddedPhones($oldAccount, $newAccount);
            }
        };
    }

    public function testCreateNewAccount()
    {
        $newAccount = $this->accountRepository->createNewAccount();

        $this->assertEquals(1, $newAccount->id);
        $this->assertEquals([], $newAccount->phones);

        $this->assertNotEmpty($this->accountRepository->getAccountIfExists(1));
    }

    public function testStoreAccount()
    {
        $this->accountRepository->storeAccount($this->account0);

        $this->assertEquals(
            '{"id":' . self::TEST_ID . ',"phones":[]}',
            $this->mockDataStore->mapGetItem('accounts', self::TEST_ID)
        );
    }

    public function testGetAccountIfExists()
    {
        $this->assertNull($this->accountRepository->getAccountIfExists(self::TEST_ID));

        $this->accountRepository->storeAccount($this->account);

        $this->assertEquals($this->account, $this->accountRepository->getAccountIfExists(self::TEST_ID));
    }

    public function testGetAccountByPhoneIfExists()
    {
        $phone = new PhoneNumber(self::TEST_PHONE1);
        $newAccount = $this->accountRepository->createNewAccount();

        $this->assertNull($this->accountRepository->getAccountByPhoneIfExists($phone));

        $newAccount->phones[] = $phone;
        $this->accountRepository->updateAccount($newAccount);

        $this->assertNotNull($this->accountRepository->getAccountByPhoneIfExists($phone));
    }

    public function testUpdateAccountDoesNotExist()
    {
        $account = new Account(77, []);
        $this->expectException(\Exception::class);
        $this->accountRepository->updateAccount($account);
    }

    public function testUpdateAccount()
    {
        $phone1  = new PhoneNumber(self::TEST_PHONE1);
        $phone2  = new PhoneNumber(self::TEST_PHONE2);
        $account = $this->accountRepository->createNewAccount();

        $account->phones[0] = $phone1;
        $this->accountRepository->updateAccount($account);

        $this->assertEquals($account, $this->accountRepository->getAccountByPhoneIfExists($phone1));
        $this->assertNull($this->accountRepository->getAccountByPhoneIfExists($phone2));

        $account->phones[0] = $phone2;
        $this->accountRepository->updateAccount($account);

        $this->assertNull($this->accountRepository->getAccountByPhoneIfExists($phone1));
        $this->assertEquals($account, $this->accountRepository->getAccountByPhoneIfExists($phone2));
    }

    public function testGetRemovedPhones()
    {
        $results = iterator_to_array($this->accountRepository->getRemovedPhones($this->account0, $this->account));
        $this->assertEmpty($results);

        $results = iterator_to_array($this->accountRepository->getRemovedPhones($this->account, $this->account0));
        $this->assertEquals([new PhoneNumber(self::TEST_PHONE1), new PhoneNumber(self::TEST_PHONE2)], $results);
    }

    public function testGetAddedPhones()
    {
        $results = iterator_to_array($this->accountRepository->getAddedPhones($this->account, $this->account0));
        $this->assertEmpty($results);

        $results = iterator_to_array($this->accountRepository->getAddedPhones($this->account0, $this->account));
        $this->assertEquals([new PhoneNumber(self::TEST_PHONE1), new PhoneNumber(self::TEST_PHONE2)], $results);
    }
}
