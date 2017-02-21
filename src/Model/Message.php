<?php

namespace MrBill\Model;

use MrBill\PhoneNumber;

class Message implements Serializable
{
    public $phone, $message, $timestamp, $isFromUser;

    /** @var int a random integer assigned to each message, to make messages unique */
    public $entropy;

    public function __construct(PhoneNumber $phone, string $message, int $timestamp, bool $isFromUser, int $entropy)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->timestamp = $timestamp;
        $this->isFromUser = $isFromUser;
        $this->entropy = $entropy;
    }

    public static function createWithEntropy(
        PhoneNumber $phone,
        string $message,
        int $timestamp,
        bool $isFromUser
    ) : Message {
        $entropy = random_int(PHP_INT_MIN, PHP_INT_MAX);

        return new Message($phone, $message, $timestamp, $isFromUser, $entropy);
    }

    public static function createFromJson(string $jsonString) : Message
    {
        $object = json_decode($jsonString);
        return new Message(
            new PhoneNumber($object->phone),
            $object->message,
            $object->timestamp,
            $object->isFromUser,
            @$object->entropy
        );
    }

    public function toJson() : string
    {
        return json_encode(
            [
                'phone' => $this->phone,
                'message' => $this->message,
                'timestamp' => $this->timestamp,
                'isFromUser' => $this->isFromUser,
                'entropy' => $this->entropy,
            ]
        );
    }
}
