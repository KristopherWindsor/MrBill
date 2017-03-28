<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use MrBill\PhoneNumber;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ExpenseRange
{
    /** @var DomainFactory */
    protected $domainFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->domainFactory = $container->get('myContainer')->get('domainFactory');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $accountId = $request->getAttribute('accountId');

        $response = $response->withHeader('Content-Type', 'application/json');
        return $response->write($this->getBoundaryOfMonthsWithExpenses($accountId));
    }

    protected function getBoundaryOfMonthsWithExpenses(int $accountId) : string
    {
        $expenseSet = $this->domainFactory->getExpenseSet($accountId);
        return json_encode($expenseSet->getBoundaryOfMonthsWithExpenses());
    }
}
