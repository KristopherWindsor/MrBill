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

    public function testGetMessageRepository()
    {
        $a = $this->repositoryFactory->getMessageRepository();
        $b = $this->repositoryFactory->getMessageRepository();

        $this->assertNotEmpty($a);
        $this->assertTrue($a === $b);
    }

    public function testGetTokenRepository()
    {
        $a = $this->repositoryFactory->getTokenRepository();
        $b = $this->repositoryFactory->getTokenRepository();

        $this->assertNotEmpty($a);
        $this->assertTrue($a === $b);
    }

    public function testGetExpenseRepository()
    {
        $a = $this->repositoryFactory->getExpenseRepository();
        $b = $this->repositoryFactory->getExpenseRepository();

        $this->assertNotEmpty($a);
        $this->assertTrue($a === $b);
    }
}
