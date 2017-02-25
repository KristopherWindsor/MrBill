<?php

namespace MrBill\Model;

abstract class Hashable implements Serializable
{
    public function getHashTag() : string
    {
        return sha1($this->toJson());
    }
}
