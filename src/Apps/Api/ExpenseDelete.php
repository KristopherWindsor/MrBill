<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ExpenseDelete
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
        $expenseId = (int) $args['id'];

        if ($this->deleteExpenseIfExists($accountId, $expenseId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    protected function deleteExpenseIfExists($accountId, $expenseId) : bool
    {
        try {
            $this->domainFactory->getExpenseSet($accountId)->deleteExpense($expenseId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
