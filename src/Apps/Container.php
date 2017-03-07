<?php

namespace MrBill\Apps;

use MrBill\Config;
use MrBill\Domain\DomainFactory;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\FileBasedDataStore;
use MrBill\Persistence\RedisDataStore;
use Predis\Client;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;

class Container implements ContainerInterface
{
    public $items = [];

    public function __construct(Config $config = null)
    {
        if ($config)
            $this->items['config'] = $config;
    }

    public function get($id)
    {
        if (array_key_exists($id, $this->items))
            return $this->items[$id];

        elseif ($id == 'config')
            return $this->items[$id] = new Config();

        elseif ($id == 'dataStore')
            return $this->items[$id] = new RedisDataStore($this->get('redis'));

        elseif ($id == 'domainFactory')
            return $this->items[$id] = new DomainFactory($this->get('repositoryFactory'));

        elseif ($id == 'redis' && $redisConfig = $this->get('config')->redis)
            return $this->items[$id] = new Client($redisConfig);

        elseif ($id == 'repositoryFactory')
            return $this->items[$id] = new RepositoryFactory($this->get('dataStore'));

        elseif ($id == 'slim')
            return $this->items[$id] = new App();

        else
            throw new class extends \Exception implements NotFoundExceptionInterface {};
    }

    public function has($id) : bool
    {
        try {
            $this->get($id);
            return true;
        } catch (\Exception $e) {
            if ($e instanceof NotFoundExceptionInterface)
                return false;
            throw $e;
        }
    }
}
