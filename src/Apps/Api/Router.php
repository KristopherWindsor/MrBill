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
                    $slim->get('/month/{year}/{month}', ExpenseReadMonth::class);
                    $slim->put('/{id}', ExpenseUpdate::class);
                    $slim->delete('/{id}', ExpenseDelete::class);
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
