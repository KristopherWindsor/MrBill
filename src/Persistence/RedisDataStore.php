<?php

namespace MrBill\Persistence;

use Predis\Client;

class RedisDataStore implements DataStore
{
    private $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function exists(string $key) : bool
    {
        return $this->redis->exists($key);
    }

    public function remove(string $key) : void
    {
        $this->redis->del([$key]);
    }

    public function scalarPut(string $key, string $value) : void
    {
        $this->redis->set($key, $value);
    }

    public function scalarGet(string $key) : ?string
    {
        return $this->redis->get($key);
    }

    public function scalarIncrement(string $key) : int
    {
        return $this->redis->incr($key);
    }

    public function listAddItem(string $key, string $value) : void
    {
        $this->redis->lpush($key, [$value]);
    }

    public function listGetAll(string $key) : array
    {
        return $this->redis->lrange($key, 0, -1);
    }

    public function mapPutItem(string $mapKey, string $itemKey, string $value) : void
    {
        $this->redis->hset($mapKey, $itemKey, $value);
    }

    public function mapGetItem(string $mapKey, string $itemKey) : ?string
    {
        return $this->redis->hget($mapKey, $itemKey);
    }

    public function mapGetAll(string $key) : array
    {
        return $this->redis->hgetall($key);
    }

    public function mapRemoveItem(string $mapKey, string $itemKey) : bool
    {
        return (bool) $this->redis->hdel($mapKey, [$itemKey]);
    }

    public function mapIncrementItem(string $mapKey, string $itemKey) : int
    {
        return $this->redis->hincrby($mapKey, $itemKey, 1);
    }
}
