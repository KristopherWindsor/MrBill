<?php

namespace MrBillTest\Unit;

use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    public function testInvalidPhone()
    {
        $this->expectException(\Exception::class);

        new PhoneNumber('140872262w6');
    }

    public function testScalar()
    {
        $phone = new PhoneNumber('14087226296');

        $this->assertSame('14087226296', $phone->scalar);
    }

    public function testToString()
    {
        $phone = new PhoneNumber('14087226296');

        $this->assertSame('14087226296', (string) $phone);
    }

    public function testJsonSerialize()
    {
        $phone = new PhoneNumber('14087226296');

        $this->assertSame(14087226296, $phone->jsonSerialize());
    }
}
