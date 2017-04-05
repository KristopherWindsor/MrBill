<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use MrBill\Domain\TokenSet;
use MrBill\Model\Expense;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\PhoneNumber;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ExpenseReadMonth
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
        return $response->write($this->getExpenses($accountId, (int) $args['year'], (int) $args['month']));
    }

    protected function getExpenses(int $accountId, int $year, int $month) : string
    {
        $resultData = [];
        $expenseSet = $this->domainFactory->getExpenseSet($accountId);

        $depreciationOptions = array_flip(ExpenseCreate::DEPRECIATION_OPTIONS);

        /** @var Expense $expense */
        foreach ($expenseSet->getExpensesForMonth($year, $month) as $id => $expense) {
            $resultData[] = [
                'id'            => $id,
                'accountId'     => $accountId,
                'timestamp'     => $expense->timestamp,
                'amountInCents' => $expense->amountInCents,
                'hashTags'      => $expense->hashTags,
                'description'   => $expense->description,
                'depreciation'  => $depreciationOptions[$expense->depreciation],
            ];
        }

        return json_encode($resultData);
    }
}
