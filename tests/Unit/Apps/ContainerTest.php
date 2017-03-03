<?php

namespace MrBillTest\Unit\Apps;

use MrBill\Apps\Container;
use MrBill\Domain\DomainFactory;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\DataStore;
use PHPUnit\Framework\TestCase;
use Slim\App;

class ContainerTest extends TestCase
{
    public function testGet()
    {
        $container = new Container();

        $this->assertTrue($container->get('dataStore') instanceof DataStore);
        $this->assertTrue($container->get('repositoryFactory') instanceof RepositoryFactory);
        $this->assertTrue($container->get('domainFactory') instanceof DomainFactory);
        $this->assertTrue($container->get('slim') instanceof App);
    }
}
