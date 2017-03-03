<?php

namespace MrBill;

class Config
{
    /** @var string */
    public $publicSiteUrl;

    /** @var array|null */
    public $redis;

    public function __construct()
    {
        if (getenv('MYREDIS_PORT_6379_TCP_ADDR')) {
            $this->redis = [
                'scheme' => 'tcp',
                'host' => getenv('MYREDIS_PORT_6379_TCP_ADDR'),
                'port' => 6379,
            ];
        }

        $this->publicSiteUrl = getenv('MR_BILL_PUBLIC_URL');
    }
}
