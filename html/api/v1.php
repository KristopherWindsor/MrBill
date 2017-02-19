<?php

use MrBill\Api\V1;
use MrBill\MessageProvider;
use MrBill\Persistence\DataStore;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

echo (new V1(new MessageProvider(new DataStore()), $_POST))->getResult();
