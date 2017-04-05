<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use MrBill\Model\Expense;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ExpenseCreate
{
    const DEPRECIATION_OPTIONS = [
        '1week'   => Expense::DEPRECIATE_1_WEEK,
        '2week'   => Expense::DEPRECIATE_2_WEEK,
        '30day'   => Expense::DEPRECIATE_30_DAYS,
        '1month'  => Expense::DEPRECIATE_1_MONTH,
        '2month'  => Expense::DEPRECIATE_2_MONTH,
        '3month'  => Expense::DEPRECIATE_3_MONTH,
        '4month'  => Expense::DEPRECIATE_4_MONTH,
        '6month'  => Expense::DEPRECIATE_6_MONTH,
        '12month' => Expense::DEPRECIATE_12_MONTH,
        '24month' => Expense::DEPRECIATE_24_MONTH,
        '1year'   => Expense::DEPRECIATE_1_YEAR,
        '2year'   => Expense::DEPRECIATE_2_YEAR,
    ];

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

        if (isset($expense['depreciation']) && (
            !is_string($expense['depreciation']) ||
            !array_key_exists($expense['depreciation'], self::DEPRECIATION_OPTIONS)
        )) return false;

        foreach ($expense['hashTags'] as $hashTag) {
            if (!is_string($hashTag))
                return false;
        }

        return true;
    }

    protected function addExpense(int $accountId, array $expense) : int
    {
        $depreciation = isset($expense['depreciation']) ? self::DEPRECIATION_OPTIONS[$expense['depreciation']] : null;

        return $this->domainFactory->getExpenseSet($accountId)->addExpense(
            new Expense(
                $accountId,
                $expense['timestamp'],
                $expense['amountInCents'],
                $expense['hashTags'],
                $expense['description'],
                $depreciation,
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
