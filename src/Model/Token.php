<?php

namespace MrBill\Model;

use Exception;
use MrBill\PhoneNumber;

class Token implements Serializable
{
    /** @var PhoneNumber */
    public $phone;
    public $documentId;
    public $secret;
    public $expiry;

    public function __construct(PhoneNumber $phoneNumber, int $documentId, string $secret, int $expiry)
    {
        if (!$documentId || !$secret || !$expiry)
            throw new Exception();

        $this->phone = $phoneNumber;
        $this->documentId = $documentId;
        $this->secret = $secret;
        $this->expiry = $expiry;
    }

    public static function createWithRandomSecret(PhoneNumber $phoneNumber, int $documentId, int $expiry) : Token
    {
        $secret = uniqid();
        return new static($phoneNumber, $documentId, $secret, $expiry);
    }

    public static function createFromMap(array $map) : Token
    {
        return new static(
            new PhoneNumber($map['phone']),
            $map['documentId'],
            $map['secret'],
            $map['expiry']
        );
    }

    public function toMap() : array
    {
        return
            [
                'phone' => $this->phone,
                'documentId' => $this->documentId,
                'secret' => $this->secret,
                'expiry' => $this->expiry,
            ];
    }
}
