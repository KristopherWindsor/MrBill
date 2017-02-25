<?php

namespace MrBill\Model;

use MrBill\PhoneNumber;

class Expense extends Hashable implements Serializable
{
    const SOURCE_TYPE_MESSAGE = '_m';

    /** @var PhoneNumber */
    public $phone;
    /** @var int */
    public $timestamp;
    /** @var int */
    public $amountInCents;
    /** @var string[] */
    public $hashTags;
    /** @var string */
    public $description;
    /** @var string */
    public $sourceType;
    /** @var string */
    public $sourceId;
    /** @var int a random integer assigned to each message, to make messages unique */
    public $entropy;

    public function __construct(
        PhoneNumber $phone,
        int $timestamp,
        int $amountInCents,
        array $hashTags,
        string $description,
        string $sourceType,
        string $sourceId,
        string $entropy
    ) {
        $this->phone = $phone;
        $this->timestamp = $timestamp;
        $this->amountInCents = $amountInCents;
        $this->hashTags = $hashTags;
        $this->description = $description;
        $this->sourceType = $sourceType;
        $this->sourceId = $sourceId;
        $this->entropy = $entropy;
    }

    public static function createFromMessageWithEntropy(
        PhoneNumber $phone,
        int $timestamp,
        int $amountInCents,
        array $hashTags,
        string $description,
        string $messageId
    ) : Expense {
        $entropy = random_int(PHP_INT_MIN, PHP_INT_MAX);

        return new Expense(
            $phone,
            $timestamp,
            $amountInCents,
            $hashTags,
            $description,
            self::SOURCE_TYPE_MESSAGE,
            $messageId,
            $entropy
        );
    }

    public static function createFromMap(array $map) : Expense
    {
        return new Expense(
            new PhoneNumber($map['phone']),
            $map['timestamp'],
            $map['amountInCents'],
            $map['hashTags'],
            $map['description'],
            $map['sourceType'],
            $map['sourceId'],
            $map['entropy']
        );
    }

    public function toMap() : array
    {
        return
            [
                'phone'         => $this->phone,
                'timestamp'     => $this->timestamp,
                'amountInCents' => $this->amountInCents,
                'hashTags'      => $this->hashTags,
                'description'   => $this->description,
                'sourceType'    => $this->sourceType,
                'sourceId'      => $this->sourceId,
                'entropy'       => $this->entropy,
            ];
    }
}