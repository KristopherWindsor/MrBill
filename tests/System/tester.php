<?php

use MrBillTest\System\HTTPCaller;

// Might be running on a Mac host machine with different settings, etc.
ini_set('zend.assertions', '1');
ini_set('assert.exception', '1');

$start = microtime(true);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/target.txt')) {
    $target = file_get_contents(__DIR__ . '/target.txt');
} else {
    die("You need to put your target machine address in target.txt\n");
}

function tester($target)
{
    $caller = new HTTPCaller($target);
    $phoneForTesting = time() . '1';

    $caller->get404();
    $caller->getFaq();
    $caller->getSleepAndWelcome2();
    $caller->getSleepAndWelcome3();

    $currentYear = date('Y');
    $currentMonth = date('n');
    $tokenSecret = null;

    $interaction = [
        ['hello', 'Hi, I\'m Mr. Bill. Just text me each time you spend'],
        ['blah', 'Not sure what you mean'],
        ['?', 'Let\'s see how I can help you!'],
        ['?', 'Every time you spend'],
        ['$7.77 #hash', 'Got it. I\'ll send you a report once I\'ve got a few more expenses'],
        ['report', ['<tr><td>#hash</td><td>7.77</td>']],
        ['4 #tag', '<Response></Response>'],
        ['report', ['<tr><td>#hash</td><td>7.77</td>', '<tr><td>#tag</td><td>4</td>']],
        ['5 #hash', '<Response></Response>'],
        ['report', ['<tr><td>#hash</td><td>12.77</td>', '<tr><td>#tag</td><td>4</td>']],
    ];
    foreach ($interaction as list($textIn, $textOut)) {
        $response = $caller->announceMessageFromTwilio($phoneForTesting, $textIn);
        fwrite(STDERR, $response . "\n\n");

        $xml = new SimpleXMLElement($response);
        assert($xml->getName() == 'Response');

        if ($textIn == 'report') {
            assert(strpos($response, 'Your report') > 0);

            $tokenSecret = explode('&s=', $xml->Message)[1];
            assert((bool) $tokenSecret);

            $report = $caller->getReport($phoneForTesting, $tokenSecret);
            fwrite(STDERR, $report . "\n\n");

            foreach ($textOut as $itemInReport)
                assert(strpos($report, $itemInReport) > 0);
        } else {
            assert(strpos($response, $textOut) > 0);
        }
    }

    $expenseData = $caller->getExpensesData($phoneForTesting, $currentYear, $currentMonth, $tokenSecret);
    fwrite(STDERR, $expenseData . "\n\n");

    $expenseItems = json_decode($expenseData);
    assert(count($expenseItems) == 3);
    $expected = [
        ['id' => 1, 'phone' => (int) $phoneForTesting, 'amountInCents' => 777, 'hashTags' => ['hash']],
        ['id' => 2, 'phone' => (int) $phoneForTesting, 'amountInCents' => 400, 'hashTags' => ['tag']],
        ['id' => 3, 'phone' => (int) $phoneForTesting, 'amountInCents' => 500, 'hashTags' => ['hash']],
    ];
    foreach ($expected as $index => $item)
        foreach ($item as $key => $value) {
            assert($expenseItems[$index]->$key === $value, "$index/$key");
        }
}

try {
    tester($target);

    $duration = round((microtime(true) - $start) * 1000);
    echo "All good here ({$duration}ms)";
} catch (AssertionError $throwable) {
    echo $throwable;
} finally {
    echo "\n";
}
