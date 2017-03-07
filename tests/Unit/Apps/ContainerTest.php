<?php

namespace MrBillTest\Unit\Apps;

use MrBill\Apps\Container;
use MrBill\Config;
use MrBill\Domain\DomainFactory;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\DataStore;
use MrBill\Persistence\MockDataStore;
use PHPUnit\Framework\TestCase;
use Predis\Client;
use Predis\Command\CommandInterface;
use Predis\Connection\ConnectionInterface;
use Slim\App;

class ContainerTest extends TestCase
{
    public function testGet()
    {
        $container = new Container();

        $this->assertTrue($container->get('config') instanceof Config);
        $this->assertTrue($container->get('slim') instanceof App);
    }

    public function testGetWithMockDataStore()
    {
        $container = new Container();

        $container->items['dataStore'] = new MockDataStore();

        $this->assertTrue($container->get('dataStore') instanceof DataStore);
        $this->assertTrue($container->get('domainFactory') instanceof DomainFactory);
        $this->assertTrue($container->get('repositoryFactory') instanceof RepositoryFactory);
    }

    public function testGetRedis()
    {
        $config = new Config();
        $config->redis = new class implements ConnectionInterface {
            public function connect() {}
            public function disconnect() {}
            public function isConnected() {return true;}
            public function writeRequest(CommandInterface $command) {}
            public function readResponse(CommandInterface $command) {return null;}
            public function executeCommand(CommandInterface $command) {return null;}
        };
        $container = new Container($config);

        $this->assertTrue($container->get('redis') instanceof Client);
    }

    public function testHas()
    {
        $container = new Container();

        // No intention to be exhaustive here
        $this->assertTrue($container->has('config'));
        $this->assertTrue($container->has('slim'));

        $this->assertFalse($container->has('blah'));
    }

    public function testProvidedConfigIsStored()
    {
        $config = new Config();

        $container = new Container($config);

        $this->assertSame($config, $container->get('config'));
    }

    public function testExceptionOnHas()
    {
        $container = new class extends Container {
            public function get($id)
            {
                throw new \Exception();
            }
        };

        $this->expectException(\Exception::class);
        $container->has('anything');
    }
}
