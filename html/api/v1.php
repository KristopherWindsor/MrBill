<?php

use MrBill\Api\V1;
use MrBill\MessageProvider;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

echo (new V1(new MessageProvider(), $_POST))->getResult();
