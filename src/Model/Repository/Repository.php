<?php

namespace MrBill\Model\Repository;

use MrBill\Persistence\DataStore;

abstract class Repository
{
    /** @var DataStore */
    protected $dataStore;

    public function __construct(DataStore $dataStore)
    {
        $this->dataStore = $dataStore;
    }
}
