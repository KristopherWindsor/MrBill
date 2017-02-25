<?php

namespace MrBillTest\Model\Repository;

use MrBill\Model\Repository\TokenRepository;
use MrBill\Model\Token;
use MrBill\Persistence\DataStore;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class TokenRepositoryTest extends TestCase
{
    /** @var Token */
    private $token;

    /** @var TokenRepository */
    private $tokenRepository;

    public function setUp()
    {
        $this->token = new Token(
            new PhoneNumber(14087226296),
            2,
            'abc123',
            1234567890
        );

        $this->tokenRepository = new TokenRepository(new DataStore());
    }

    public function testPutAndDoesExist()
    {
        $this->tokenRepository->persistToken($this->token);
        $tokenIfExists = $this->tokenRepository->getTokenIfExists(
            $this->token->phone,
            $this->token->documentId
        );

        $this->assertNotEmpty($tokenIfExists);
        $this->assertEquals($this->token, $tokenIfExists);
    }

    public function testDeleteAndDoesNotExist()
    {
        $this->tokenRepository->deleteToken(
            $this->token->phone,
            $this->token->documentId
        );
        $tokenIfExists = $this->tokenRepository->getTokenIfExists(
            $this->token->phone,
            $this->token->documentId
        );

        $this->assertEmpty($tokenIfExists);
    }
}
