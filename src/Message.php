<?php

namespace MrBill;

class Message
{
    public $userPhone, $message, $timestamp, $isFromUser;

    public function __construct(int $userPhone, string $message, int $timestamp, bool $isFromUser)
    {
        $this->userPhone = $userPhone;
        $this->message = $message;
        $this->timestamp = $timestamp;
        $this->isFromUser = $isFromUser;
    }

    public function isHelpRequest() : bool
    {
        return $this->isFromUser && strtolower(trim($this->message)) === 'help';
    }
}
