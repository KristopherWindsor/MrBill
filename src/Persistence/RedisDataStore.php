<?php

namespace MrBill\Persistence;

use Predis\Client;

class RedisDataStore implements DataStore
{
    private $redis;

    public function __construct()
    {
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host'   => getenv('MYREDIS_PORT_6379_TCP_ADDR'),
            'port'   => 6379,
        ]);
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

    public function mapPutItem(string $mapKey, string $itemKey, $value) : void
    {
        $this->redis->hset($mapKey, $itemKey, $value);
    }

    public function mapGetAll(string $key) : array
    {
        return $this->redis->hgetall($key);
    }
}
