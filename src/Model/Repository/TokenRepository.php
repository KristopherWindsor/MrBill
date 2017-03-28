<?php

namespace MrBill\Model\Repository;

use MrBill\PhoneNumber;
use MrBill\Model\Token;

/**
 * Represents all tokens tied to a phone number.
 *
 * Typically, a user needs to request data before accessing it.
 * A token (key) is made at the time of request and verified when the data is accessed.
 */
class TokenRepository extends Repository
{
    public function persistToken(Token $token) : Token
    {
        $this->dataStore->scalarPut(
            $this->getDataStoreKey($token->accountId, $token->documentId),
            json_encode($token->toMap())
        );
        return $token;
    }

    public function getTokenIfExists(int $accountId, int $documentId) : ?Token
    {
        $key = $this->getDataStoreKey($accountId, $documentId);
        if (!$this->dataStore->exists($key))
            return null;

        $tokenString = $this->dataStore->scalarGet($key);
        return Token::createFromMap(json_decode($tokenString, true));
    }

    public function deleteToken(int $accountId, int $documentId) : void
    {
        $key = $this->getDataStoreKey($accountId, $documentId);

        $this->dataStore->remove($key);
    }

    protected function getDataStoreKey(int $accountId, int $documentId) : string
    {
        return 'token:' . $accountId . ':' . $documentId;
    }
}
