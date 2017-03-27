<?php

namespace MrBillTest\Unit\Model\Repository;

use MrBill\Persistence\MockDataStore;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\DataStore;
use PHPUnit\Framework\TestCase;

class RepositoryFactoryTest extends TestCase
{
    /** @var RepositoryFactory */
    private $repositoryFactory;

    public function setUp()
    {
        $this->repositoryFactory = new RepositoryFactory(new MockDataStore());
    }

    public function listOfMethodNames()
    {
        return [
            ['getAccountRepository'],
            ['getMessageRepository'],
            ['getTokenRepository'],
            ['getExpenseRepository'],
        ];
    }

    /**
     * @param string $methodName
     * @dataProvider listOfMethodNames
     */
    public function testGetters(string $methodName)
    {
        $a = $this->repositoryFactory->$methodName();
        $b = $this->repositoryFactory->$methodName();

        $this->assertNotEmpty($a);
        $this->assertTrue($a === $b);
    }
}
