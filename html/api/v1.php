<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

echo (new MrBill\Api\V1($_POST))->getResult();
