<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\FileBasedDataStore;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

class Router
{
    public function getSlimAppWithRoutes(ContainerInterface $container) : App
    {
        $slim = $container->get('slim');
        $slim->any(
            '/twilio/v1',
            function (Request $request, Response $response, array $args) use ($container) {
                $factory = $container->get('domainFactory');

                $handler = new TwilioV1($factory, $request->getParsedBody() ?: []);
                $resultXml = $handler->getResult();

                $response->getBody()->write($resultXml);
            }
        );
        return $slim;
    }
}
