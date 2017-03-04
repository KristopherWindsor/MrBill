<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use MrBill\Model\Expense;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\PhoneNumber;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class Expenses
{
    /** @var RepositoryFactory */
    protected $repositoryFactory;

    /** @var DomainFactory */
    protected $domainFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->repositoryFactory = $container->get('myContainer')->get('repositoryFactory');
        $this->domainFactory = $container->get('myContainer')->get('domainFactory');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $phone = new PhoneNumber($args['phone']);

        if ($this->isSecretValid($phone, $args['token'])) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->write($this->getExpenses($phone, (int) $args['year'], (int) $args['month']));
        } else {
            $response = $response->withStatus(401);
        }

        return $response;
    }

    protected function isSecretValid(PhoneNumber $phone, string $secret) : bool
    {
        // TODO use domain instead - for magical document ID
        $token = $this->repositoryFactory->getTokenRepository()->getTokenIfExists($phone, 1);

        return $token && !$token->isExpired() && $token->secret == $secret;
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
