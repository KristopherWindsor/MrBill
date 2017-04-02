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

        $expense = $request->getParsedBody();

        if (!$this->isExpenseValid($expense)) {
            return $response->withStatus(400);
        }

        $expenseId = $this->addExpense($accountId, $expense);

        $response = $response->withHeader('Content-Type', 'application/json');
        return $response->write((string) $expenseId);
    }

    protected function isExpenseValid(array $expense) : bool
    {
        if (empty($expense['timestamp']) || !is_int($expense['timestamp'])) return false;
        if (empty($expense['amountInCents']) || !is_int($expense['amountInCents'])) return false;
        if (!isset($expense['description']) || !is_string($expense['description'])) return false;
        if (empty($expense['hashTags']) || !is_array($expense['hashTags'])) return false;

        foreach ($expense['hashTags'] as $hashTag) {
            if (!is_string($hashTag))
                return false;
        }

        return true;
    }

    protected function addExpense(int $accountId, array $expense) : int
    {
        return $this->domainFactory->getExpenseSet($accountId)->addExpense(
            new Expense(
                $accountId,
                $expense['timestamp'],
                $expense['amountInCents'],
                $expense['hashTags'],
                $expense['description'],
                Expense::STATUS_RESOLVED,
                [
                    'fromAPI' => [
                        'time' => time(),
                    ]
                ]
            )
        );
    }
}
