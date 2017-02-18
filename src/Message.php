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
