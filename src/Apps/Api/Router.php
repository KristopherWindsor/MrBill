<?php

namespace MrBill\Apps\Api;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

class Router
{
    public function getSlimAppWithRoutes(ContainerInterface $container) : App
    {
        $slim = $container->get('slim');
        $slim->getContainer()['myContainer'] = $container;
        $slim->any('/twilio/v1', TwilioV1::class);
        $slim->get('/expenses/{phone}/{year}/{month}/{token}', Expenses::class);
        return $slim;
    }

    public function __invoke(ContainerInterface $container) : void
    {
        $this->getSlimAppWithRoutes($container)->run();
    }
}
