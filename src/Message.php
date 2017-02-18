<?php

namespace MrBill;

class Message
{
    public $userPhone, $message, $timestamp, $isFromUser;

    public static function createFromJson($jsonString) : Message
    {
        $object = json_decode($jsonString);
        return new Message($object->phone, $object->message, $object->timestamp, $object->isFromUser);
    }

    public function __construct(int $userPhone, string $message, int $timestamp, bool $isFromUser)
    {
        $this->userPhone = $userPhone;
        $this->message = $message;
        $this->timestamp = $timestamp;
        $this->isFromUser = $isFromUser;
    }

    public function isHelpRequest() : bool
    {
        return $this->isFromUser && trim($this->message) === '?';
    }

    public function isAnswer() : bool
    {
        $message = trim(strtolower($this->message));
        return $this->isFromUser && in_array($message, ['y', 'n', 'yes', 'no', 'a', 'b', 'c', 'd']);
    }

    public function isExpenseRecord() : bool
    {
        if (!$this->isFromUser) return false;

        $parts = array_filter(explode(' ', str_replace("\n", ' ', $this->message)), 'trim');
        if (count($parts) < 2) return false;

        $amount = str_replace(['$', '¢', '£', '€', '.'], '', $parts[0]);
        if (!ctype_digit($amount)) return false;

        $totalHashtags = 0;
        for ($i = 1; $i < count($parts); $i++) {
            if (substr($parts[$i], 0, 1) == '#') {
                $totalHashtags++;
            }
        }
        return $totalHashtags > 0;
    }

    public function isUnknownIntent() : bool
    {
        return $this->isFromUser && !$this->isHelpRequest() && !$this->isAnswer() && !$this->isExpenseRecord();
    }

    public function toJson() : string
    {
        return json_encode(
            [
                'phone' => $this->userPhone,
                'message' => $this->message,
                'timestamp' => $this->timestamp,
                'isFromUser' => $this->isFromUser,
            ]
        );
    }
}
