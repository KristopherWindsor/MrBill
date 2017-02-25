<?php

namespace MrBillTest\Model\Repository;

use Generator;
use MrBill\Persistence\DataStore;

class MockDataStore extends DataStore
{
    public $storage = [];

    public function exists(string $key) : bool
    {
        return isset($this->storage[$key]);
    }

    public function get(string $key) : Generator
    {
        if (isset($this->storage[$key]))
            foreach ($this->storage[$key] as $value)
                yield $value;
    }

    public function append(string $key, string $item) : void
    {
        $this->storage[$key][] = $item;
    }

    public function put(string $key, string $item) : void
    {
        $this->storage[$key] = [$item];
    }

    public function remove(string $key) : void
    {
        unset($this->storage[$key]);
    }
}
