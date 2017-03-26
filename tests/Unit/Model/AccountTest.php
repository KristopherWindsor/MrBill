<?php

namespace MrBillTest\Unit\Model;

use MrBill\Model\Account;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    const TEST_ID = 123;
    const TEST_PHONE_1 = 14087226296;
    const TEST_PHONE_2 = 14087226297;

    /** @var Account */
    private $account0;

    /** @var Account */
    private $account1;

    /** @var Account */
    private $account2;

    public function setUp()
    {
        $phone1 = new PhoneNumber(self::TEST_PHONE_1);
        $phone2 = new PhoneNumber(self::TEST_PHONE_2);

        $this->account0 = new Account(self::TEST_ID, []);
        $this->account1 = new Account(self::TEST_ID, [$phone1]);
        $this->account2 = new Account(self::TEST_ID, [$phone1, $phone2]);
    }

    public function testConstructor()
    {
        $phone1 = new PhoneNumber(self::TEST_PHONE_1);
        $phone2 = new PhoneNumber(self::TEST_PHONE_2);

        $this->assertEquals(self::TEST_ID, $this->account0->id);

        $this->assertEquals([],                 $this->account0->phones);
        $this->assertEquals([$phone1],          $this->account1->phones);
        $this->assertEquals([$phone1, $phone2], $this->account2->phones);
    }

    public function testToMap()
    {
        $this->assertEquals(
            '{"id":' . $this->account2->id . ',"phones":[' . self::TEST_PHONE_1 . ',' . self::TEST_PHONE_2 . ']}',
            json_encode($this->account2->toMap())
        );
    }

    public function testFromMap()
    {
        $loadedExpense = Account::createFromMap($this->account2->toMap());

        $this->assertEquals($this->account2->id, $loadedExpense->id);
        $this->assertEquals($this->account2->phones, $loadedExpense->phones);
    }
}
