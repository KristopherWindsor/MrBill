<?php

namespace MrBill\Model;

use MrBill\PhoneNumber;

class Account implements Serializable
{
    /** @var int */
    public $id;
    /** @var PhoneNumber[] */
    public $phones;

    public function __construct(
        int $id,
        array $phones
    ) {
        $this->id = $id;
        $this->phones = $phones;
    }

    public static function createFromMap(array $map) : Account
    {
        $phones = array_map(
            function ($item) {
                return new PhoneNumber($item);
            },
            $map['phones']
        );

        return new Account(
            $map['id'],
            $phones
        );
    }

    public function toMap() : array
    {
        return
            [
                'id'     => $this->id,
                'phones' => $this->phones,
            ];
    }
}
