<?php

namespace MrBillTest\Unit\Model;

use MrBill\Model\Hashable;
use MrBill\Model\Serializable;
use PHPUnit\Framework\TestCase;

class HashableTest extends TestCase
{
    public function testHashSame()
    {
        $item1 = $this->hashableFactory(['a']);
        $item2 = $this->hashableFactory(['a']);

        $this->assertEquals($item1->getHash(), $item2->getHash());
    }

    public function testHashDifferent()
    {
        $item1 = $this->hashableFactory(['a']);
        $item2 = $this->hashableFactory(['b']);

        $this->assertNotEquals($item1->getHash(), $item2->getHash());
    }

    public function hashableFactory($data) : Hashable
    {
        return new class($data) extends Hashable implements Serializable {
            private $data;

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function toMap() : array {
                return $this->data;
            }

            public static function createFromMap(array $map) {}
        };
    }
}
