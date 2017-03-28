<?php

namespace MrBill\Domain;

use MrBill\Model\Repository\TokenRepository;
use MrBill\Model\Token;
use MrBill\PhoneNumber;

class TokenSet
{
    const REPORT_ID = 1;

    /** @var int */
    protected $accountId;

    /** @var TokenRepository */
    protected $tokenRepository;

    public function __construct(
        int $accountId,
        TokenRepository $tokenRepository
    ) {
        $this->accountId = $accountId;
        $this->tokenRepository = $tokenRepository;
    }

    public function hasValidTokenForDocumentWithSecret(int $id, string $secret) : bool
    {
        $token = $this->tokenRepository->getTokenIfExists($this->accountId, $id);

        return $token && $token->expiry >= time() && $token->secret == $secret;
    }

    public function getSecretIfActive(int $documentId) : ?string
    {
        $EXPIRY_WINDOW = 3600;

        $token = $this->tokenRepository->getTokenIfExists($this->accountId, $documentId);

        return $token && $token->expiry >= time() + $EXPIRY_WINDOW ? $token->secret : null;
    }

    public function createActiveTokenForDocument(int $documentId) : string
    {
        $token = new Token(
            $this->accountId,
            $documentId,
            dechex(random_int(pow(2, 48), pow(2, 52) - 1)),
            time() + 3600 * 24 * 30
        );

        $this->tokenRepository->persistToken($token);

        return $token->secret;
    }
}
