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
        $slim->post('/twilio/v1', TwilioV1::class);

        $slim
            ->group(
                '/expenses',
                function () use ($slim) {
                    $slim->post('', ExpenseCreate::class);
                    $slim->get('/range', ExpenseRange::class);
                    $slim->get('/month/{year:[0-9]+}/{month:[0-9]+}', ExpenseReadMonth::class);
                    $slim->put('/{id:[0-9]+}', ExpenseUpdate::class);
                    $slim->delete('/{id:[0-9]+}', ExpenseDelete::class);
                }
            )
            ->add(ReportAuth::class);

        return $slim;
    }

    public function __invoke(ContainerInterface $container) : void
    {
        $this->getSlimAppWithRoutes($container)->run();
    }
}
