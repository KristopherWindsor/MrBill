<?php

namespace MrBillTest\Unit\Model\Repository;

use MrBill\Persistence\MockDataStore;
use MrBill\Model\Repository\TokenRepository;
use MrBill\Model\Token;
use PHPUnit\Framework\TestCase;

class TokenRepositoryTest extends TestCase
{
    const TEST_ID = 123;

    /** @var Token */
    private $token;

    /** @var MockDataStore */
    private $mockDataStore;

    /** @var TokenRepository */
    private $tokenRepository;

    public function setUp()
    {
        $this->token = new Token(
            self::TEST_ID,
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
                'token:123:2' =>
                    '{"accountId":123,"documentId":2,"secret":"abc123","expiry":1234567890}'
            ],
            $this->mockDataStore->storage
        );
    }

    public function testDeleteToken()
    {
        $this->persistToken();

        $this->tokenRepository->deleteToken(
            $this->token->accountId,
            $this->token->documentId
        );

        $this->assertEmpty($this->mockDataStore->storage);
    }

    public function testGetTokenWhenDoesExist()
    {
        $this->persistToken();

        $tokenIfExists = $this->tokenRepository->getTokenIfExists(
            $this->token->accountId,
            $this->token->documentId
        );

        $this->assertNotEmpty($tokenIfExists);
        $this->assertEquals($this->token, $tokenIfExists);
    }

    public function testGetTokenWhenDoesNotExist()
    {
        $tokenIfExists = $this->tokenRepository->getTokenIfExists(
            $this->token->accountId,
            $this->token->documentId
        );

        $this->assertEmpty($tokenIfExists);
    }
}
