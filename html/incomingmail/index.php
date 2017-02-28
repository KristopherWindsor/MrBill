<?php

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';

// No clue what I'm doing

$raw = file_get_contents('php://input');
$headers = json_encode(getallheaders());
$key = 'mail' . time();
(new \MrBill\Persistence\FileBasedDataStore())->listAddItem($key, $raw . $headers);
