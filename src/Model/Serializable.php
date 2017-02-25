<?php

namespace MrBill\Model;

interface Serializable
{
    public function toMap() : array;
    public static function createFromMap(array $map);
}
