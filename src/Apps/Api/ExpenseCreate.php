<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use MrBill\Model\Expense;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ExpenseCreate
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

        $expenseInput = $request->getParsedBody();
        $expenseValidator = new ExpenseValidator();

        if (!$expenseValidator->isExpenseInputValid($expenseInput)) {
            return $response->withStatus(400);
        }

        $expenseId = $this->domainFactory->getExpenseSet($accountId)
            ->addExpense($expenseValidator->getExpenseFromInput($accountId, $expenseInput));

        $response = $response->withHeader('Content-Type', 'application/json');
        return $response->write((string) $expenseId);
    }
}
