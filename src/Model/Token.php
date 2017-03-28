<?php

namespace MrBill\Model;

use Exception;
use MrBill\PhoneNumber;

class Token implements Serializable
{
    /** @var PhoneNumber */
    public $accountId;
    public $documentId;
    public $secret;
    public $expiry;

    public function __construct(int $accountId, int $documentId, string $secret, int $expiry)
    {
        if (!$documentId || !$secret || !$expiry)
            throw new Exception();

        $this->accountId = $accountId;
        $this->documentId = $documentId;
        $this->secret = $secret;
        $this->expiry = $expiry;
    }

    public static function createWithRandomSecret(int $accountId, int $documentId, int $expiry) : Token
    {
        $secret = uniqid();
        return new static($accountId, $documentId, $secret, $expiry);
    }

    public static function createFromMap(array $map) : Token
    {
        return new static(
            $map['accountId'],
            $map['documentId'],
            $map['secret'],
            $map['expiry']
        );
    }

    public function toMap() : array
    {
        return
            [
                'accountId' => $this->accountId,
                'documentId' => $this->documentId,
                'secret' => $this->secret,
                'expiry' => $this->expiry,
            ];
    }
}
