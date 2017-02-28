<?php

use MrBill\Apps\Api\V1;
use MrBill\Domain\DomainFactory;
use MrBill\Persistence\FileBasedDataStore;
use MrBill\Model\Repository\RepositoryFactory;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

$factory = new DomainFactory(new RepositoryFactory(new FileBasedDataStore()));

echo (new V1($factory, $_POST))->getResult();
