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
        $phone = $request->getAttribute('phone');
        assert($phone instanceof PhoneNumber);

        $response = $response->withHeader('Content-Type', 'application/json');
        return $response->write($this->getExpenses($phone, (int) $args['year'], (int) $args['month']));
    }

    protected function getExpenses(PhoneNumber $phone, int $year, int $month) : string
    {
        $resultData = [];
        $expenseSet = $this->domainFactory->getExpenseSet($phone);

        /** @var Expense $expense */
        foreach ($expenseSet->getExpensesForMonth($year, $month) as $id => $expense) {
            $resultData[] = [
                'id'            => $id,
                'phone'         => $phone,
                'timestamp'     => $expense->timestamp,
                'amountInCents' => $expense->amountInCents,
                'hashTags'      => $expense->hashTags,
                'description'   => $expense->description,
            ];
        }

        return json_encode($resultData);
    }
}
