<?php

namespace MrBill\Apps\Api;

use MrBill\Domain\TokenSet;
use MrBill\Domain\DomainFactory;
use MrBill\PhoneNumber;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

class ReportAuth
{
    /** @var DomainFactory */
    protected $domainFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->domainFactory = $container->get('myContainer')->get('domainFactory');
    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) : ResponseInterface {

        $accountId = (int) reset($request->getHeader('account'));
        if (!$accountId || !$this->domainFactory->getAccount($accountId)) {
            return $response->withStatus(401);
        }

        $isSecretValid = $this->domainFactory->getTokenSet($accountId)->hasValidTokenForDocumentWithSecret(
            TokenSet::REPORT_ID,
            (string) reset($request->getHeader('token'))
        );
        if (!$isSecretValid) {
            return $response->withStatus(401);
        }

        return $next($request->withAttribute('accountId', $accountId), $response);
    }
}
