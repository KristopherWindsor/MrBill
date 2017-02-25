<?php

namespace MrBill\Model;

abstract class Hashable implements Serializable
{
    public function getHash() : string
    {
        return sha1(json_encode($this->toMap()));
    }
}
