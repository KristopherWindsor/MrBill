<?php

namespace MrBill\Model;

use MrBill\PhoneNumber;

class Message extends Hashable implements Serializable
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
        $entropy = random_int(1 << 16, 1 << 32);

        return new Message($phone, $message, $timestamp, $isFromUser, $entropy);
    }

    public static function createFromMap(array $map) : Message
    {
        return new Message(
            new PhoneNumber($map['phone']),
            $map['message'],
            $map['timestamp'],
            $map['isFromUser'],
            @$map['entropy']
        );
    }

    public function toMap() : array
    {
        return
            [
                'phone' => $this->phone,
                'message' => $this->message,
                'timestamp' => $this->timestamp,
                'isFromUser' => $this->isFromUser,
                'entropy' => $this->entropy,
            ];
    }
}
