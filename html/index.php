<?php

use MrBill\Apps\Api\Router;
use MrBill\Apps\Container;

require_once dirname(__DIR__) . '/vendor/autoload.php';

(new Router)->getSlimAppWithRoutes(new Container())->run();
