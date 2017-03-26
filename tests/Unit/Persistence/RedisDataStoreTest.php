<?php

namespace MrBillTest\Unit\Persistence;

use MrBill\Persistence\RedisDataStore;
use PHPUnit\Framework\TestCase;
use Predis\Client;

/**
 * Achieves code statement coverage by inspecting the calls made to the Predis Client
 */
class RedisDataStoreTest extends TestCase
{
    private $mockPredisClient;

    /** @var RedisDataStore */
    private $redisDataStore;

    public function setUp()
    {
        $this->mockPredisClient = $this->getMockedPredisClient();
        $this->redisDataStore = new RedisDataStore($this->mockPredisClient);
    }

    protected function getMockedPredisClient() : Client
    {
        return new class extends Client {
            public $lastCallInfo;

            public function __construct() {
            }

            public function __call($commandID, $arguments)
            {
                $this->lastCallInfo = [$commandID, $arguments];

                if ($commandID == 'exists')
                    return false;
                if ($commandID == 'get' || $commandID == 'hget')
                    return '';
                if ($commandID == 'incr' || $commandID == 'hincrby')
                    return 0;
                if ($commandID == 'lrange' || $commandID == 'hgetall')
                    return [];
            }
        };
    }

    public function testExists()
    {
        $this->redisDataStore->exists('key');
        $this->assertEquals(['exists', ['key']], $this->mockPredisClient->lastCallInfo);
    }

    public function testRemove()
    {
        $this->redisDataStore->remove('key');
        $this->assertEquals(['del', [['key']]], $this->mockPredisClient->lastCallInfo);
    }

    public function testScalarPut()
    {
        $this->redisDataStore->scalarPut('key', 'value');
        $this->assertEquals(['set', ['key', 'value']], $this->mockPredisClient->lastCallInfo);
    }

    public function testScalarGet()
    {
        $this->redisDataStore->scalarGet('key');
        $this->assertEquals(['get', ['key']], $this->mockPredisClient->lastCallInfo);
    }

    public function testScalarIncrement()
    {
        $this->redisDataStore->scalarIncrement('key');
        $this->assertEquals(['incr', ['key']], $this->mockPredisClient->lastCallInfo);
    }

    public function testListAddItem()
    {
        $this->redisDataStore->listAddItem('key', 'value');
        $this->assertEquals(['lpush', ['key', ['value']]], $this->mockPredisClient->lastCallInfo);
    }

    public function testListGetAll()
    {
        $this->redisDataStore->listGetAll('key');
        $this->assertEquals(['lrange', ['key', 0, -1]], $this->mockPredisClient->lastCallInfo);
    }

    public function testMapPutItem()
    {
        $this->redisDataStore->mapPutItem('key', 'x', 'value');
        $this->assertEquals(['hset', ['key', 'x', 'value']], $this->mockPredisClient->lastCallInfo);
    }

    public function testMapGetItem()
    {
        $this->redisDataStore->mapGetItem('key', 'item');
        $this->assertEquals(['hget', ['key', 'item']], $this->mockPredisClient->lastCallInfo);
    }

    public function testMapGetAll()
    {
        $this->redisDataStore->mapGetAll('key');
        $this->assertEquals(['hgetall', ['key']], $this->mockPredisClient->lastCallInfo);
    }

    public function testMapIncrementItem()
    {
        $this->redisDataStore->mapIncrementItem('key', 'value');
        $this->assertEquals(['hincrby', ['key', 'value', 1]], $this->mockPredisClient->lastCallInfo);
    }
}
