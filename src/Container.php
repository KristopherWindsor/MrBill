<?php

namespace MrBill;

use MrBill\Domain\DomainFactory;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\FileBasedDataStore;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\App;

class Container implements ContainerInterface
{
    protected $items = [];

    public function get($id)
    {
        if (array_key_exists($id, $this->items))
            return $this->items[$id];
        elseif ($id == 'dataStore')
            return $this->items[$id] = new FileBasedDataStore();
        elseif ($id == 'repositoryFactory')
            return $this->items[$id] = new RepositoryFactory($this->get('dataStore'));
        elseif ($id == 'domainFactory')
            return $this->items[$id] = new DomainFactory($this->get('repositoryFactory'));
        elseif ($id == 'slim')
            return $this->items[$id] = new App();
        else
            throw new class implements NotFoundExceptionInterface {};
    }

    public function has($id)
    {
        return isset($this->items[$id]);
    }
}
