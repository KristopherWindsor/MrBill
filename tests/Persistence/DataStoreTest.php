<?php

namespace MrBill\Persistence;

use PHPUnit\Framework\TestCase;

class DataStoreTest extends TestCase
{
    /** @var DataStore */
    protected $dataStore;
    protected $key;

    public function setUp()
    {
        $this->dataStore = new DataStore();
        $this->key = uniqid();
    }

    public function tearDown()
    {
        $this->dataStore->remove($this->key);
    }

    public function testExistsAfterPut()
    {
        $this->assertFalse($this->dataStore->exists($this->key));
        $this->dataStore->put($this->key, 'value');
        $this->assertTrue($this->dataStore->exists($this->key));
    }

    public function testAppendAndGet()
    {
        $this->dataStore->append($this->key, 'item1');
        $this->dataStore->append($this->key, 'item2');
        $index = -1;
        $expected = ['item1', 'item2'];
        foreach ($this->dataStore->get($this->key) as $index => $value) {
            $this->assertEquals($expected[$index], $value);
        }
        $this->assertEquals(1, $index);
    }

    public function testDelete()
    {
        $this->dataStore->put($this->key, 'value');
        $this->assertTrue($this->dataStore->exists($this->key));

        $this->dataStore->remove($this->key);
        $this->assertFalse($this->dataStore->exists($this->key));
    }
}
