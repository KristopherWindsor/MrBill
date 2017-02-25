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
        $this->dataStore->put($this->getDataStoreKey($token->phone, $token->documentId), json_encode($token->toMap()));
        return $token;
    }

    public function getTokenIfExists(PhoneNumber $phoneNumber, int $documentId) : ?Token
    {
        $key = $this->getDataStoreKey($phoneNumber, $documentId);
        if (!$this->dataStore->exists($key))
            return null;

        $tokenString = $this->dataStore->get($key)->current();
        return Token::createFromMap(json_decode($tokenString, true));
    }

    public function deleteToken(PhoneNumber $phoneNumber, int $documentId) : void
    {
        $key = $this->getDataStoreKey($phoneNumber, $documentId);

        $this->dataStore->remove($key);
    }

    protected function getDataStoreKey(PhoneNumber $phoneNumber, int $documentId) : string
    {
        return 'token' . $phoneNumber . '_' . $documentId;
    }
}
