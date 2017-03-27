<?php

namespace MrBill\Model;

use MrBill\PhoneNumber;

class Message extends Hashable implements Serializable
{
    public $accountId;
    public $phone, $message, $timestamp, $isFromUser;

    /** @var int a random integer assigned to each message, to make messages unique */
    public $entropy;

    public function __construct(
        int $accountId,
        PhoneNumber $phone,
        string $message,
        int $timestamp,
        bool $isFromUser,
        int $entropy
    ) {
        $this->accountId = $accountId;
        $this->phone = $phone;
        $this->message = $message;
        $this->timestamp = $timestamp;
        $this->isFromUser = $isFromUser;
        $this->entropy = $entropy;
    }

    public static function createWithEntropy(
        int $accountId,
        PhoneNumber $phone,
        string $message,
        int $timestamp,
        bool $isFromUser
    ) : Message {
        $entropy = random_int(1 << 16, 1 << 32);

        return new Message($accountId, $phone, $message, $timestamp, $isFromUser, $entropy);
    }

    public static function createFromMap(array $map) : Message
    {
        return new Message(
            $map['accountId'],
            new PhoneNumber($map['phone']),
            $map['message'],
            $map['timestamp'],
            $map['isFromUser'],
            $map['entropy']
        );
    }

    public function toMap() : array
    {
        return
            [
                'accountId' => $this->accountId,
                'phone' => $this->phone,
                'message' => $this->message,
                'timestamp' => $this->timestamp,
                'isFromUser' => $this->isFromUser,
                'entropy' => $this->entropy,
            ];
    }
}
