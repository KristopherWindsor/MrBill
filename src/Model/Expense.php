<?php

namespace MrBill\Model;

class Expense extends Hashable implements Serializable
{
    const STATUS_FROM_MESSAGE                = '_m';
    const STATUS_FROM_ALERT                  = '_a';
    const STATUS_FROM_ALERT_UNKNOWN_HASHTAGS = '_u';
    const STATUS_RESOLVED                    = '_r';

    const DEPRECIATE_1_WEEK   = '1w';
    const DEPRECIATE_2_WEEK   = '2w';
    const DEPRECIATE_30_DAYS  = '30d';
    const DEPRECIATE_1_MONTH  = '1m';
    const DEPRECIATE_2_MONTH  = '2m';
    const DEPRECIATE_3_MONTH  = '3m';
    const DEPRECIATE_4_MONTH  = '4m';
    const DEPRECIATE_6_MONTH  = '6m';
    const DEPRECIATE_12_MONTH = '12m';
    const DEPRECIATE_24_MONTH = '24m';
    const DEPRECIATE_1_YEAR   = '1y';
    const DEPRECIATE_2_YEAR   = '2y';

    /** @var int */
    public $accountId;
    /** @var int */
    public $timestamp;
    /** @var int */
    public $amountInCents;
    /** @var string[] */
    public $hashTags;
    /** @var string */
    public $description;
    /** @var string|null */
    public $depreciation;
    /** @var string */
    public $sourceType;
    /** @var array */
    public $sourceInfo;
    /** @var int a random integer assigned to each message, to make messages unique */
    public $entropy;

    public function __construct(
        int $accountId,
        int $timestamp,
        int $amountInCents,
        array $hashTags,
        string $description,
        ?string $depreciation,
        string $sourceType,
        array $sourceInfo
    ) {
        $this->accountId = $accountId;
        $this->timestamp = $timestamp;
        $this->amountInCents = $amountInCents;
        $this->hashTags = $hashTags;
        $this->description = $description;
        $this->depreciation = $depreciation;
        $this->sourceType = $sourceType;
        $this->sourceInfo = $sourceInfo;
    }

    public static function createFromMessage(
        int $accountId,
        int $timestamp,
        int $amountInCents,
        array $hashTags,
        string $description,
        array $sourceInfo
    ) : Expense {

        return new Expense(
            $accountId,
            $timestamp,
            $amountInCents,
            $hashTags,
            $description,
            null,
            self::STATUS_FROM_MESSAGE,
            $sourceInfo
        );
    }

    public static function createFromMap(array $map) : Expense
    {
        return new Expense(
            $map['accountId'],
            $map['timestamp'],
            $map['amountInCents'],
            $map['hashTags'],
            $map['description'],
            $map['depreciation'] ?? null,
            $map['sourceType'],
            $map['sourceInfo']
        );
    }

    public function toMap() : array
    {
        return
            [
                'accountId'     => $this->accountId,
                'timestamp'     => $this->timestamp,
                'amountInCents' => $this->amountInCents,
                'hashTags'      => $this->hashTags,
                'description'   => $this->description,
                'depreciation'  => $this->depreciation,
                'sourceType'    => $this->sourceType,
                'sourceInfo'    => $this->sourceInfo,
            ];
    }
}
