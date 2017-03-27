<?php

namespace MrBillTest\Unit\Apps\Api;

use MrBill\Apps\Api\TwilioV1;
use MrBill\Apps\Container;
use MrBill\Domain\DomainFactory;
use MrBill\Domain\TokenSet;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\PhoneNumber;
use MrBill\Persistence\MockDataStore;
use PHPUnit\Framework\TestCase;
use Slim\App;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Uri;

class TwilioV1Test extends TestCase
{
    const TEST_PHONE = 14087226296;
    const TEST_TIMESTAMP = 1487403557;

    const PUBLIC_URL = 'http://example.com';

    /** @var PhoneNumber */
    private $testPhone;

    /** @var DomainFactory */
    private $domainFactory;

    public function setUp()
    {
        $this->testPhone = new PhoneNumber(self::TEST_PHONE);

        $repositoryFactory = new RepositoryFactory(new MockDataStore());

        $this->domainFactory = new DomainFactory($repositoryFactory);
    }

    public function testInvalidRequest()
    {
        $slim = new App();

        $v1 = new TwilioV1($slim->getContainer());
        $v1($slim->getContainer()['request'], $slim->getContainer()['response'], []);
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Something is wrong.</Message></Response>',
            $v1->getResult()
        );
    }

    public function doWelcomeMessage() : string
    {
        $v1 = new TwilioV1();
        $v1->run($this->domainFactory, self::PUBLIC_URL, new PhoneNumber('14087226296'), 'hello');

        return $v1->getResult();
    }

    public function testWelcomeMessage()
    {
        $expected =
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Hi, I\'m Mr. Bill. Just text me each time you ' .
                'spend $$, and I\'ll help you track expenses. That\'s right... you keep track of your expenses by ' .
                'texting them to me.</Message><Redirect>' . self::PUBLIC_URL . '/api/sleep.php?' .
                'sleep=6&amp;content=welcome2</Redirect></Response>';

        $this->assertEquals($expected, $this->doWelcomeMessage());
    }

    public function testExpenseRecord()
    {
        $this->doWelcomeMessage(); // Get those out of the way

        $expected = [
            1 => '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Got it. I\'ll send you a report once ' .
                'I\'ve got a few more expenses.</Message></Response>',
            5 => '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Keep them coming!</Message></Response>',
        ];

        for ($i = 1; $i < 7; $i++) {
            $v1 = new TwilioV1();
            $v1->run($this->domainFactory, self::PUBLIC_URL, new PhoneNumber(self::TEST_PHONE), '$7 #tag');
            $this->assertEquals(
                $expected[$i] ?? '<?xml version="1.0" encoding="UTF-8" ?><Response></Response>',
                $v1->getResult(),
                'Case ' . $i
            );
        }
    }

    public function testHelpRequest()
    {
        $this->doWelcomeMessage(); // Get those out of the way

        $v1 = new TwilioV1();
        $v1->run($this->domainFactory, self::PUBLIC_URL, new PhoneNumber(self::TEST_PHONE), ' ?');
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>1/5 Let\'s see how I can help you! Text "?" again to cycle through the help messages.</Message></Response>',
            $v1->getResult()
        );

        $v1 = new TwilioV1();
        $v1->run($this->domainFactory, self::PUBLIC_URL, new PhoneNumber(self::TEST_PHONE), ' ?');
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>2/5 Every time you spend $$, send me a text like: 8.99 #eatout #lunch lunch with friends</Message></Response>',
            $v1->getResult()
        );
    }

    public function testCreateReportAndGetReply() : string
    {
        $this->doWelcomeMessage();

        $v1 = new TwilioV1();
        $v1->run($this->domainFactory, self::PUBLIC_URL, new PhoneNumber('14087226296'), 'report');

        $secret = $this->domainFactory->getTokenSet($this->testPhone)->getSecretIfActive(TokenSet::REPORT_ID);

        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8" ?><Response><Message>Your report! ' .
                self::PUBLIC_URL . '/report?p=' . $this->testPhone . '&amp;s=' . $secret .
                '</Message></Response>',
            $v1->getResult()
        );

        return $v1->getResult();
    }

    /**
     * @depends testCreateReportAndGetReply
     * @param string $manualRunResult
     */
    public function testInvokeSameAsManualRun(string $manualRunResult)
    {
        $this->doWelcomeMessage();

        $uri = Uri::createFromString('http://doesnotmatter.com');
        $request = (new Request('POST', $uri, new Headers(), [], [], new RequestBody()))
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded');

        $request->getBody()->write('MessageSid=abc&From=' . self::TEST_PHONE . '&Body=' . 'report');
        $request->getBody()->rewind();

        $slimContainer = (new App)->getContainer();
        $slimContainer['myContainer'] = new Container();
        $slimContainer['myContainer']->items['domainFactory'] = $this->domainFactory;
        $slimContainer['myContainer']->get('config')->publicUrl = self::PUBLIC_URL;

        $v1 = new TwilioV1($slimContainer);
        $v1($request, $slimContainer['response'], []);

        // Ignore random token secret in check
        $part1 = explode('&amp;s=', $manualRunResult)[0];
        $part2 = explode('&amp;s=', $v1->getResult())[0];
        $this->assertNotEmpty($part1);
        $this->assertEquals($part1, $part2);
    }
}
