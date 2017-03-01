<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\FileBasedDataStore;
use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

class Router
{
    public function getSlimAppWithRoutes(App $vanillaSlimApp) : App
    {
        $vanillaSlimApp->any('/twilio/v1', function (Request $request, Response $response, array $args) {
            $factory = new DomainFactory(new RepositoryFactory(new FileBasedDataStore()));

            $handler = new TwilioV1($factory, $request->getParsedBody() ?: []);
            $resultXml = $handler->getResult();

            $response->getBody()->write($resultXml);
        });
        return $vanillaSlimApp;
    }
}
