<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\DomainFactory;
use MrBill\Domain\TokenSet;
use MrBill\PhoneNumber;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class ExpensesRange
{
    /** @var DomainFactory */
    protected $domainFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->domainFactory = $container->get('myContainer')->get('domainFactory');
    }

    public function __invoke(Request $request, Response $response, $args)
    {
        $phone = new PhoneNumber($args['phone']);

        $isSecretValid = $this->domainFactory->getTokenSet($phone)
            ->hasValidTokenForDocumentWithSecret(TokenSet::REPORT_ID, $args['token']);

        if ($isSecretValid) {
            $response = $response->withHeader('Content-Type', 'application/json');
            $response->write($this->getBoundaryOfMonthsWithExpenses($phone));
        } else {
            $response = $response->withStatus(401);
        }

        return $response;
    }

    protected function getBoundaryOfMonthsWithExpenses(PhoneNumber $phone) : string
    {
        $expenseSet = $this->domainFactory->getExpenseSet($phone);
        return json_encode($expenseSet->getBoundaryOfMonthsWithExpenses());
    }
}
