<?php

$content = @$_GET['content'];
if ($content != 'welcome2' && $content != 'welcome3') exit;

$sleep = min(4, max(0, (int) @$_GET['sleep']));
if ($sleep) sleep($sleep);

header('Content-Type: application/xml');
readfile(__DIR__ . '/../assets/welcome2.xml');
