<?php

namespace MrBill\Persistence;

/**
 * This interface is based on the data structures of redis.
 */
interface DataStore
{
    public function exists(string $key) : bool;
    public function remove(string $key) : void;

    public function scalarPut(string $key, string $value) : void;
    public function scalarGet(string $key) : ?string;
    public function scalarIncrement(string $key) : int;

    public function listAddItem(string $key, string $value) : void;
    public function listGetAll(string $key) : array;

    public function mapPutItem(string $mapKey, string $itemKey, string $value) : void;
    public function mapGetItem(string $mapKey, string $itemKey) : ?string;
    public function mapGetAll(string $key) : array;
    public function mapRemoveItem(string $mapKey, string $itemKey) : bool;
    public function mapIncrementItem(string $mapKey, string $itemKey) : int;
}
