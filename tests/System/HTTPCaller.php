<?php

namespace MrBillTest\System;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;

class HTTPCaller
{
    private $domain;

    /** @var Client */
    private $guzzle;

    public function __construct(string $protocolAndDomainOrIpWithOptionalPort)
    {
        $this->domain = $protocolAndDomainOrIpWithOptionalPort;
        $this->guzzle = new Client([
            'base_uri' => $protocolAndDomainOrIpWithOptionalPort,
            'timeout'  => 2.0,
        ]);
    }

    public function getFaq() : string
    {
        $response = $this->guzzle->request('GET', 'faq.html');
        assert($response->getStatusCode() == 200);

        $body = (string) $response->getBody();
        assert(strpos($body, '<title>Mr. Bill FAQ</title>') > 0);

        return $body;
    }

    public function getSleepAndWelcome2() : string
    {
        $response = $this->guzzle->request('GET', 'api/sleep.php?sleep=0&content=welcome2');
        assert($response->getStatusCode() == 200);

        $body = (string) $response->getBody();
        assert(strpos($body, 'We\'ll use hashtags for categories') > 0);

        return $body;
    }

    public function getSleepAndWelcome3() : string
    {
        $response = $this->guzzle->request('GET', 'api/sleep.php?sleep=0&content=welcome3');
        assert($response->getStatusCode() == 200);

        $body = (string) $response->getBody();
        assert(strpos($body, 'After you\'ve entered a few expenses') > 0);

        return $body;
    }

    public function get404() : string
    {
        $body = '';
        try {
            $this->guzzle->request('GET', 'abcdef');
            assert(false);
        } catch (ClientException $e) {
            $response = $e->getResponse();
            assert($response->getStatusCode() == 404);

            $body = (string) $response->getBody();
        }

        return $body;
    }

    public function announceMessageFromTwilio($fromPhone, $messageBody) : string
    {
        $request = new Request(
            'POST',
            $this->domain . '/twilio/v1',
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            http_build_query([
                'MessageSid' => uniqid(),
                'From' => $fromPhone,
                'Body' => $messageBody,
            ])
        );

        $response = $this->guzzle->send($request);
        assert($response->getStatusCode() == 200);

        return (string) $response->getBody();
    }

    public function getReport(int $accountId, $token) : string
    {
        $response = $this->guzzle->request('GET', $this->domain . '/report?a=' . $accountId . '&s=' . $token);
        assert($response->getStatusCode() == 200);

        $body = (string) $response->getBody();
        assert(strpos($body, '<h1>Your Expenses</h1>') > 0);

        return $body;
    }

    public function getExpensesData(int $accountId, $year, $month, string $secret) : string
    {
        $response = $this->guzzle->request(
            'GET',
            "{$this->domain}/expenses/month/{$year}/{$month}",
            ['headers' => ['account' => $accountId, 'token' => $secret]]
        );
        assert($response->getStatusCode() == 200);

        return (string) $response->getBody();
    }

    public function getExpensesRange(int $accountId, string $secret)
    {
        $response = $this->guzzle->request(
            'GET',
            "{$this->domain}/expenses/range",
            ['headers' => ['account' => $accountId, 'token' => $secret]]
        );
        assert($response->getStatusCode() == 200);

        return (string) $response->getBody();
    }

    public function addExpense(
        int $accountId,
        string $secret,
        int $timestamp,
        int $amountInCents,
        array $hashTags,
        string $description,
        ?string $depreciation
    ) : int {
        $request = new Request(
            'POST',
            $this->domain . '/expenses',
            [
                'Content-Type' => 'application/json',
                'account'      => $accountId,
                'token'        => $secret,
            ],
            json_encode([
                'timestamp'     => $timestamp,
                'amountInCents' => $amountInCents,
                'hashTags'      => $hashTags,
                'description'   => $description,
                'depreciation'  => $depreciation,
            ])
        );

        $response = $this->guzzle->send($request);
        assert($response->getStatusCode() == 200);

        return intval((string) $response->getBody());
    }

    public function updateExpense(
        int $accountId,
        string $secret,
        int $expenseId,
        int $timestamp,
        int $amountInCents,
        array $hashTags,
        string $description,
        ?string $depreciation
    ) : void {
        $request = new Request(
            'PUT',
            $this->domain . '/expenses/' . $expenseId,
            [
                'Content-Type' => 'application/json',
                'account'      => $accountId,
                'token'        => $secret,
            ],
            json_encode([
                'timestamp'     => $timestamp,
                'amountInCents' => $amountInCents,
                'hashTags'      => $hashTags,
                'description'   => $description,
                'depreciation'  => $depreciation,
            ])
        );

        $response = $this->guzzle->send($request);
        assert($response->getStatusCode() == 200);
    }

    public function deleteExpense(int $accountId, string $secret, int $expenseId) : void
    {
        $response = $this->guzzle->request(
            'DELETE',
            "{$this->domain}/expenses/{$expenseId}",
            ['headers' => ['account' => $accountId, 'token' => $secret]]
        );
        assert($response->getStatusCode() == 200);
    }
}
