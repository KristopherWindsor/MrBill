<?php

namespace MrBill\Persistence;

class FileBasedDataStore implements DataStore
{
    private const DATA_DIRECTORY = '/var/www/data';

    public function exists(string $key) : bool
    {
        return file_exists($this->getFileNameForKey($key));
    }

    public function remove(string $key) : void
    {
        @unlink($this->getFileNameForKey($key));
    }

    public function scalarPut(string $key, string $value) : void
    {
        file_put_contents($this->getFileNameForKey($key), $value);
    }

    public function scalarGet(string $key) : ?string
    {
        if (!$this->exists($key))
            return null;
        $file = $this->getFileNameForKey($key);
        return file_get_contents($file);
    }

    public function scalarIncrement(string $key) : int
    {
        $value = $this->scalarGet($key) + 1;
        $this->scalarPut($key, $value . '');
        return $value;
    }

    public function listAddItem(string $key, string $value) : void
    {
        $list = $this->listGetAll($key);
        array_unshift($list, $value);
        $this->scalarPut($key, json_encode($list));
    }

    public function listGetAll(string $key) : array
    {
        if (!$this->exists($key))
            return [];

        $value = $this->scalarGet($key);
        if (!$value)
            return [];

        $list = json_decode($value, true);
        return $list ?: [];
    }

    public function mapPutItem(string $mapKey, string $itemKey, $value) : void
    {
        $map = $this->mapGetAll($mapKey);
        $map[$itemKey] = $value;
        $this->scalarPut($mapKey, json_encode($map));
    }

    public function mapGetAll(string $key) : array
    {
        return $this->listGetAll($key);
    }

    protected function getFileNameForKey(string $key) : string
    {
        return self::DATA_DIRECTORY . '/' . $key;
    }
}
