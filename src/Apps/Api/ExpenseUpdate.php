<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use MrBill\Model\Expense;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ExpenseUpdate
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
        $expenseInput = $request->getParsedBody();
        $expenseValidator = new ExpenseValidator();

        if (!$expenseValidator->isExpenseInputValid($expenseInput)) {
            return $response->withStatus(400);
        }

        $wasUpdated = $this->domainFactory->getExpenseSet($accountId)->updateIfExists(
            $expenseId,
            $expenseValidator->getExpenseFromInput($accountId, $expenseInput)
        );
        if (!$wasUpdated) {
            return $response->withStatus(404);
        }

        return $response;
    }
}
