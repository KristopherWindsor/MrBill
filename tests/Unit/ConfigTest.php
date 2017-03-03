<?php

namespace MrBillTest\Unit;

use MrBill\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected $originalEnvSettings;

    public function setUp()
    {
        $this->originalEnvSettings = [
            getenv('MYREDIS_PORT_6379_TCP_ADDR'),
            getenv('MR_BILL_PUBLIC_URL')
        ];
    }

    public function tearDown()
    {
        list($a, $b) = $this->originalEnvSettings;

        if ($a === false) {
            putenv('MYREDIS_PORT_6379_TCP_ADDR');
        } else {
            putenv('MYREDIS_PORT_6379_TCP_ADDR=' . $a);
        }

        putenv('MR_BILL_PUBLIC_URL=' . $b);
    }

    public function testAll()
    {
        putenv('MYREDIS_PORT_6379_TCP_ADDR=1.2.3.4');
        putenv('MR_BILL_PUBLIC_URL=http://example.com');

        $config = new Config();

        $this->assertEquals(
            [
                'scheme' => 'tcp',
                'host'   => '1.2.3.4',
                'port'   => 6379,
            ],
            $config->redis
        );

        $this->assertEquals('http://example.com', $config->publicUrl);
    }
}
