<?php

namespace MrBillTest\Model\Repository;

use MrBill\Persistence\MockDataStore;
use MrBill\Model\Repository\TokenRepository;
use MrBill\Model\Token;
use MrBill\PhoneNumber;
use PHPUnit\Framework\TestCase;

class TokenRepositoryTest extends TestCase
{
    /** @var Token */
    private $token;

    /** @var MockDataStore */
    private $mockDataStore;

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

        $this->mockDataStore = new MockDataStore();

        $this->tokenRepository = new TokenRepository($this->mockDataStore);
    }

    protected function persistToken()
    {
        $this->tokenRepository->persistToken($this->token);
    }

    public function testPersistToken()
    {
        $this->persistToken();

        $this->assertEquals(
            [
                'token:14087226296:2' =>
                    '{"phone":14087226296,"documentId":2,"secret":"abc123","expiry":1234567890}'
            ],
            $this->mockDataStore->storage
        );
    }

    public function testDeleteToken()
    {
        $this->persistToken();

        $this->tokenRepository->deleteToken(
            $this->token->phone,
            $this->token->documentId
        );

        $this->assertEmpty($this->mockDataStore->storage);
    }

    public function testGetTokenWhenDoesExist()
    {
        $this->persistToken();

        $tokenIfExists = $this->tokenRepository->getTokenIfExists(
            $this->token->phone,
            $this->token->documentId
        );

        $this->assertNotEmpty($tokenIfExists);
        $this->assertEquals($this->token, $tokenIfExists);
    }

    public function testGetTokenWhenDoesNotExist()
    {
        $tokenIfExists = $this->tokenRepository->getTokenIfExists(
            $this->token->phone,
            $this->token->documentId
        );

        $this->assertEmpty($tokenIfExists);
    }
}
