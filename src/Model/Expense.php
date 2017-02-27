<?php

namespace MrBill\Model;

use MrBill\PhoneNumber;

class Expense extends Hashable implements Serializable
{
    const STATUS_FROM_MESSAGE                = '_m';
    const STATUS_FROM_ALERT                  = '_a';
    const STATUS_FROM_ALERT_UNKNOWN_HASHTAGS = '_u';
    const STATUS_RESOLVED                    = '_r';

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
    /** @var array */
    public $sourceInfo;
    /** @var int a random integer assigned to each message, to make messages unique */
    public $entropy;

    public function __construct(
        PhoneNumber $phone,
        int $timestamp,
        int $amountInCents,
        array $hashTags,
        string $description,
        string $sourceType,
        array $sourceInfo,
        string $entropy
    ) {
        $this->phone = $phone;
        $this->timestamp = $timestamp;
        $this->amountInCents = $amountInCents;
        $this->hashTags = $hashTags;
        $this->description = $description;
        $this->sourceType = $sourceType;
        $this->sourceInfo = $sourceInfo;
        $this->entropy = $entropy;
    }

    public static function createFromMessageWithEntropy(
        PhoneNumber $phone,
        int $timestamp,
        int $amountInCents,
        array $hashTags,
        string $description,
        array $sourceInfo
    ) : Expense {
        $entropy = random_int(1 << 16, 1 << 32);

        return new Expense(
            $phone,
            $timestamp,
            $amountInCents,
            $hashTags,
            $description,
            self::STATUS_FROM_MESSAGE,
            $sourceInfo,
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
            $map['sourceInfo'],
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
                'sourceInfo'    => $this->sourceInfo,
                'entropy'       => $this->entropy,
            ];
    }
}
