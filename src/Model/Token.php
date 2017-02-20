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

    public static function createFromJson(string $jsonString) : Token
    {
        $object = json_decode($jsonString);
        return new static(
            new PhoneNumber($object->phone),
            $object->documentId,
            $object->secret,
            $object->expiry
        );
    }

    public function toJson() : string
    {
        return json_encode(
            [
                'phone' => $this->phone,
                'documentId' => $this->documentId,
                'secret' => $this->secret,
                'expiry' => $this->expiry,
            ]
        );
    }

    public function isExpired() : bool
    {
        return $this->expiry < time();
    }
}
