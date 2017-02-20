<?php

use MrBill\Apps\Api\V1;
use MrBill\Domain\ConversationFactory;
use MrBill\Persistence\DataStore;
use MrBill\Model\Repository\MessageRepository;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

echo (new V1(new ConversationFactory(new MessageRepository(new DataStore())), $_POST))->getResult();
