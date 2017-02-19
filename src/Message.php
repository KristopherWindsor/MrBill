<?php

namespace MrBill;

class Message
{
    public $phone, $message, $timestamp, $isFromUser;

    public static function createFromJson(string $jsonString) : Message
    {
        $object = json_decode($jsonString);
        return new Message(
            new PhoneNumber($object->phone),
            $object->message,
            $object->timestamp,
            $object->isFromUser
        );
    }

    public function __construct(PhoneNumber $phone, string $message, int $timestamp, bool $isFromUser)
    {
        $this->phone = $phone;
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
        return ExpenseRecord::getExpenseRecordIfValid($this->message) !== null;
    }

    public function isReportRequest() : bool
    {
        $message = trim(strtolower($this->message));
        return $this->isFromUser && in_array($message, ['report']);
    }

    public function isUnknownIntent() : bool
    {
        return $this->isFromUser &&
            !$this->isHelpRequest() &&
            !$this->isAnswer() &&
            !$this->isExpenseRecord() &&
            !$this->isReportRequest();
    }

    public function toJson() : string
    {
        return json_encode(
            [
                'phone' => $this->phone,
                'message' => $this->message,
                'timestamp' => $this->timestamp,
                'isFromUser' => $this->isFromUser,
            ]
        );
    }
}
