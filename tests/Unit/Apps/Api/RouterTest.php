<?php

namespace MrBillTest\Unit\Apps\Api;

use MrBill\Apps\Api\Router;
use MrBill\Apps\Container;
use PHPUnit\Framework\TestCase;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use Slim\Http\Uri;

class RouterTest extends TestCase
{
    public function testTwilioV1()
    {
        $request = $this->requestFactory('POST', '/twilio/v1', 'MessageSid=123&From=14087226296&Body=');

        $app = (new Router)->getSlimAppWithRoutes(new Container());

        /** @var Response $response */
        $response = $app($request, new Response());
        $response->getBody()->rewind();

        $this->assertEquals(
            200,
            $response->getStatusCode()
        );
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Something is wrong.</Message></Response>',
            $response->getBody()->getContents()
        );
    }

    public function test404() : string
    {
        $request = $this->requestFactory('GET', '/abcd');

        $app = (new Router)->getSlimAppWithRoutes(new Container());

        /** @var Response $response */
        $response = $app($request, new Response());
        $response->getBody()->rewind();

        $this->assertEquals(
            404,
            $response->getStatusCode()
        );

        return (string) $response->getBody();
    }

    /**
     * @depends test404
     * @param string $fourOhFourResponse
     */
    public function testInvokeSameAsRegularUsage(string $fourOhFourResponse)
    {
        $container = new Container();
        $container->get('slim')->getContainer()['request'] = $this->requestFactory('GET', '/abcd');

        ob_start();
        (new Router)($container);
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertEquals($fourOhFourResponse, $output);
    }

    protected function requestFactory(string $method, string $path, string $body = '') : Request
    {
        $env = Environment::mock();
        $uri = Uri::createFromString('https://example.com:443' . $path);
        $headers = Headers::createFromEnvironment($env);
        $cookies = [];
        $serverParams = $env->all();
        $uploadedFiles = UploadedFile::createFromEnvironment($env);

        $request = new Request($method, $uri, $headers, $cookies, $serverParams, new RequestBody(), $uploadedFiles);
        $request = $request->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $request->getBody()->write($body);
        $request->getBody()->rewind();

        return $request;
    }
}
