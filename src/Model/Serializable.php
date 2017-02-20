<?php

namespace MrBill\Model;

interface Serializable
{
    public function toJson() : string;
    public static function createFromJson(string $json);
}
