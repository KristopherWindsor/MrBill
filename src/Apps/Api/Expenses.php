<?php

namespace MrBill\Apps\Api;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Expenses
{
    /** @var ContainerInterface */
    protected $slimContainer;

    /** @var ContainerInterface */
    protected $myContainer;

    public function __construct(ContainerInterface $container)
    {
        $this->slimContainer = $container;
        $this->myContainer = $container['myContainer'];
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        // TODO
        return $response;
    }
}
