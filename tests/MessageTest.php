<?php

namespace MrBill;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/vendor/autoload.php'; // TODO move to bootstrap

class MessageTest extends TestCase
{
    public function testIsHelp()
    {
        $scenarios = [
            [true, new Message(14087226296, 'heLp ', time(), true)],
            [false, new Message(14087226296, 'heLp ', time(), false)],
            [false, new Message(14087226296, 'hello', time(), true)],
            [false, new Message(14087226296, 'hello', time(), false)],
        ];

        foreach ($scenarios as $index => [$expected, $message]) {
            $this->assertEquals($expected, $message->isHelpRequest(), 'Case ' . $index);
        }
    }
}
