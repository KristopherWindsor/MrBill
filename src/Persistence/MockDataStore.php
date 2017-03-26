<?php

namespace MrBill\Persistence;

class MockDataStore implements DataStore
{
    public $storage = [];

    public function exists(string $key) : bool
    {
        return isset($this->storage[$key]);
    }

    public function remove(string $key) : void
    {
        unset($this->storage[$key]);
    }

    public function scalarPut(string $key, string $value) : void
    {
        $this->storage[$key] = $value;
    }

    public function scalarGet(string $key) : ?string
    {
        return $this->storage[$key] ?? null;
    }

    public function scalarIncrement(string $key) : int
    {
        $value = $this->scalarGet($key) + 1;
        $this->scalarPut($key, $value . '');
        return $value;
    }

    public function listAddItem(string $key, string $value) : void
    {
        if (empty($this->storage[$key])) {
            $this->storage[$key] = [];
        }
        array_unshift($this->storage[$key], $value);
    }

    public function listGetAll(string $key) : array
    {
        return $this->storage[$key] ?? [];
    }

    public function mapPutItem(string $mapKey, string $itemKey, string $value) : void
    {
        $this->storage[$mapKey][$itemKey] = $value;
    }

    public function mapGetItem(string $mapKey, string $itemKey) : ?string
    {
        return $this->storage[$mapKey][$itemKey] ?? null;
    }

    public function mapGetAll(string $key) : array
    {
        return $this->storage[$key] ?? [];
    }

    public function mapRemoveItem(string $mapKey, string $itemKey) : bool
    {
        if (isset($this->storage[$mapKey][$itemKey])) {
            unset($this->storage[$mapKey][$itemKey]);
            return true;
        }
        return false;
    }

    public function mapIncrementItem(string $mapKey, string $itemKey) : int
    {
        $currentValue = $this->storage[$mapKey][$itemKey] ?? 0;
        $this->mapPutItem($mapKey, $itemKey, (string) ($currentValue + 1));
        return $currentValue + 1;
    }
}
