<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

// No clue what I'm doing

$raw = file_get_contents('php://input');
$key = 'mail' . time();
(new \MrBill\Persistence\DataStore())->put($key, $raw);
