<?php

namespace MrBill\Model;

use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    const TEST_PHONE = 14087226296;
    const TEST_DOCUMENT_ID = 2;
    const TEST_SECRET = 'abc123';
    const TEST_EXPIRY = 1234567890;

    /** @var PhoneNumber */
    protected $phoneNumber;

    /** @var Token */
    protected $token;

    public function setUp()
    {
        $this->phoneNumber = new PhoneNumber(self::TEST_PHONE);

        $this->token = new Token(
            $this->phoneNumber,
            self::TEST_DOCUMENT_ID,
            self::TEST_SECRET,
            self::TEST_EXPIRY
        );
    }

    public function testConstructedInstance()
    {
        $this->assertEquals($this->phoneNumber, $this->token->phone);
        $this->assertEquals(self::TEST_DOCUMENT_ID, $this->token->documentId);
        $this->assertEquals(self::TEST_SECRET, $this->token->secret);
        $this->assertEquals(self::TEST_EXPIRY, $this->token->expiry);
    }

    public function testCreateWithRandomSecret()
    {
        $this->token = Token::createWithRandomSecret(
            $this->phoneNumber,
            self::TEST_DOCUMENT_ID,
            self::TEST_EXPIRY
        );

        $this->assertEquals($this->phoneNumber, $this->token->phone);
        $this->assertEquals(self::TEST_DOCUMENT_ID, $this->token->documentId);
        $this->assertNotEmpty($this->token->secret);
        $this->assertEquals(self::TEST_EXPIRY, $this->token->expiry);
    }

    public function testToMap()
    {
        $json = json_encode($this->token->toMap());
        $this->assertEquals(
            '{"phone":14087226296,"documentId":2,"secret":"abc123","expiry":1234567890}',
            $json
        );
    }

    public function testCreateFromMap()
    {
        $map = $this->token->toMap();

        $this->token = Token::createFromMap($map);

        $this->testConstructedInstance();
    }

    public function testIsExpiredYes()
    {
        $this->token->expiry = time() - 10;

        $this->assertTrue($this->token->isExpired());
    }

    public function testIsExpiredNo()
    {
        $this->token->expiry = time() + 10;

        $this->assertFalse($this->token->isExpired());
    }
}
