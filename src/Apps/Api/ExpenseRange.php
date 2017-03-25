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
        $phone = $request->getAttribute('phone');
        assert($phone instanceof PhoneNumber);

        $response = $response->withHeader('Content-Type', 'application/json');
        return $response->write($this->getBoundaryOfMonthsWithExpenses($phone));
    }

    protected function getBoundaryOfMonthsWithExpenses(PhoneNumber $phone) : string
    {
        $expenseSet = $this->domainFactory->getExpenseSet($phone);
        return json_encode($expenseSet->getBoundaryOfMonthsWithExpenses());
    }
}
