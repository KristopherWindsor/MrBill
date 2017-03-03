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
        $slim->any(
            '/twilio/v1',
            function (Request $request, Response $response, array $args) use ($container) {
                $factory = $container->get('domainFactory');

                $handler = new TwilioV1(
                    $factory,
                    $request->getParsedBody() ?: [],
                    $container->get('config')->publicUrl
                );
                $resultXml = $handler->getResult();

                $response->getBody()->write($resultXml);
            }
        );
        $slim->get('/expenses/{phone}/{year}/{month}/{token}', Expenses::class);
        return $slim;
    }

    public function __invoke(ContainerInterface $container) : void
    {
        $this->getSlimAppWithRoutes($container)->run();
    }
}
