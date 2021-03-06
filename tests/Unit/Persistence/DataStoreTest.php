<?php

namespace MrBillTest\Unit\Persistence;

use Generator;
use MrBill\Apps\Container;
use MrBill\Persistence\DataStore;
use MrBill\Persistence\FileBasedDataStore;
use MrBill\Persistence\MockDataStore;
use MrBill\Persistence\RedisDataStore;
use PHPUnit\Framework\TestCase;

class DataStoreTest extends TestCase
{
    public function getAllDataStores() : Generator
    {
        yield [new MockDataStore()];
        yield [new FileBasedDataStore()];

        $container = new Container();
        if ($container->has('redis'))
            yield [new RedisDataStore($container->get('redis'))];
    }

    /**
     * @dataProvider getAllDataStores
     * @param DataStore $dataStore
     */
    public function testExistsAndPutAndRemove(DataStore $dataStore)
    {
        $dataStore->remove('key');
        $this->assertFalse($dataStore->exists('key'));

        $dataStore->scalarPut('key', 'value');
        $this->assertTrue($dataStore->exists('key'));

        $dataStore->remove('key');
        $this->assertFalse($dataStore->exists('key'));
    }

    /**
     * @dataProvider getAllDataStores
     * @param DataStore $dataStore
     */
    public function testScalarGetAndPut(DataStore $dataStore)
    {
        $value = '[{abc"}]';

        $this->assertEquals(null, $dataStore->scalarGet('key'));

        $dataStore->scalarPut('key', $value);

        $this->assertEquals($value, $dataStore->scalarGet('key'));

        $dataStore->remove('key');
    }

    /**
     * @dataProvider getAllDataStores
     * @param DataStore $dataStore
     */
    public function testScalarIncrement(DataStore $dataStore)
    {
        $this->assertEquals(null, $dataStore->scalarGet('key'));

        $this->assertEquals(1, $dataStore->scalarIncrement('key'));
        $this->assertEquals(2, $dataStore->scalarIncrement('key'));

        $this->assertEquals(2, $dataStore->scalarGet('key'));

        $dataStore->remove('key');
    }

    /**
     * @dataProvider getAllDataStores
     * @param DataStore $dataStore
     */
    public function testListAddItemAndGetAll(DataStore $dataStore)
    {
        $this->assertEmpty($dataStore->listGetAll('key'));

        $dataStore->listAddItem('key', 'a');

        $this->assertEquals(['a'], $dataStore->listGetAll('key'));

        $dataStore->listAddItem('key', 'b');

        $this->assertEquals(['b', 'a'], $dataStore->listGetAll('key'));

        $dataStore->remove('key');
    }

    /**
     * @dataProvider getAllDataStores
     * @param DataStore $dataStore
     */
    public function testMapPutItemAndGetAll(DataStore $dataStore)
    {
        $this->assertEmpty($dataStore->mapGetAll('key'));

        $dataStore->mapPutItem('key', 'ak', 'av');

        $this->assertEquals(['ak' => 'av'], $dataStore->mapGetAll('key'));

        $dataStore->mapPutItem('key', 'bk', 'bv');

        $this->assertEquals(['bk' => 'bv', 'ak' => 'av'], $dataStore->mapGetAll('key'));

        $dataStore->remove('key');
    }

    /**
     * @dataProvider getAllDataStores
     * @param DataStore $dataStore
     */
    public function testMapPutAndGet(DataStore $dataStore)
    {
        $this->assertNull($dataStore->mapGetItem('key', 'itemKey'));

        $dataStore->mapPutItem('key', 'itemKey', 'val');

        $this->assertEquals('val', $dataStore->mapGetItem('key', 'itemKey'));

        $dataStore->remove('key');
    }

    /**
     * @dataProvider getAllDataStores
     * @param DataStore $dataStore
     */
    public function testPutAndRemove(DataStore $dataStore)
    {
        $this->assertFalse($dataStore->mapRemoveItem('key', 'itemKey'));

        $dataStore->mapPutItem('key', 'itemKey', 'val');

        $this->assertTrue($dataStore->mapRemoveItem('key', 'itemKey'));
        $this->assertFalse($dataStore->mapRemoveItem('key', 'itemKey'));
    }

    /**
     * @dataProvider getAllDataStores
     * @param DataStore $dataStore
     */
    public function testMapIncrement(DataStore $dataStore)
    {
        $dataStore->mapIncrementItem('key', 'a');
        $this->assertEquals(1, $dataStore->mapGetAll('key')['a']);

        $dataStore->mapIncrementItem('key', 'a');
        $dataStore->mapIncrementItem('key', 'b');
        $this->assertEquals(2, $dataStore->mapGetAll('key')['a']);
        $this->assertEquals(1, $dataStore->mapGetAll('key')['b']);

        $dataStore->remove('key');
    }
}
