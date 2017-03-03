<?php

namespace MrBillTest\Unit\Apps;

use MrBill\Apps\Container;
use MrBill\Config;
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

        $this->assertTrue($container->get('config') instanceof Config);
        $this->assertTrue($container->get('dataStore') instanceof DataStore);
        $this->assertTrue($container->get('domainFactory') instanceof DomainFactory);
        $this->assertTrue($container->get('repositoryFactory') instanceof RepositoryFactory);
        $this->assertTrue($container->get('slim') instanceof App);
    }

    public function testHas()
    {
        $container = new Container();

        // No intention to be exhaustive here
        $this->assertTrue($container->has('config'));
        $this->assertTrue($container->has('dataStore'));

        $this->assertFalse($container->has('blah'));
    }
}
