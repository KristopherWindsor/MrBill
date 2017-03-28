<?php

namespace MrBillTest\Unit\Model;

use MrBill\Model\Token;
use PHPUnit\Framework\TestCase;

class TokenTest extends TestCase
{
    const TEST_ID = 123;
    const TEST_DOCUMENT_ID = 2;
    const TEST_SECRET = 'abc123';
    const TEST_EXPIRY = 1234567890;

    /** @var Token */
    protected $token;

    public function setUp()
    {
        $this->token = new Token(
            self::TEST_ID,
            self::TEST_DOCUMENT_ID,
            self::TEST_SECRET,
            self::TEST_EXPIRY
        );
    }

    public function testInvalidParams()
    {
        $this->expectException(\Exception::class);

        new Token(self::TEST_ID, self::TEST_DOCUMENT_ID, self::TEST_SECRET, 0);
    }

    public function testConstructedInstance()
    {
        $this->assertEquals(self::TEST_ID, $this->token->accountId);
        $this->assertEquals(self::TEST_DOCUMENT_ID, $this->token->documentId);
        $this->assertEquals(self::TEST_SECRET, $this->token->secret);
        $this->assertEquals(self::TEST_EXPIRY, $this->token->expiry);
    }

    public function testCreateWithRandomSecret()
    {
        $this->token = Token::createWithRandomSecret(
            self::TEST_ID,
            self::TEST_DOCUMENT_ID,
            self::TEST_EXPIRY
        );

        $this->assertEquals(self::TEST_ID, $this->token->accountId);
        $this->assertEquals(self::TEST_DOCUMENT_ID, $this->token->documentId);
        $this->assertNotEmpty($this->token->secret);
        $this->assertEquals(self::TEST_EXPIRY, $this->token->expiry);
    }

    public function testToMap()
    {
        $json = json_encode($this->token->toMap());
        $this->assertEquals(
            '{"accountId":123,"documentId":2,"secret":"abc123","expiry":1234567890}',
            $json
        );
    }

    public function testCreateFromMap()
    {
        $map = $this->token->toMap();

        $this->token = Token::createFromMap($map);

        $this->testConstructedInstance();
    }
}
