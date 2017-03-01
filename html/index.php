<?php

use MrBill\Apps\Api\Router;

require_once dirname(__DIR__) . '/vendor/autoload.php';

(new Router)->getSlimAppWithRoutes(new Slim\App())->run();
