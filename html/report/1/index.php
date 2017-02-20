<?php
use MrBill\Apps\Report\Report1;
use MrBill\Domain\ConversationFactory;
use MrBill\Model\Repository\MessageRepository;
use MrBill\Persistence\DataStore;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';
$_GET['phone']=14087226296;//test
$report = new Report1(new ConversationFactory(new MessageRepository(new DataStore())), $_GET);

if ($report->hasInitializationError()) {
    http_response_code(400);
    exit;
}

?><!DOCTYPE html>
<html>
<head>
    <title>Your Expenses &mdash; Mr. Bill</title>
</head>
<body>
    <h1>Your Expenses</h1>

    <p>This report is in beta.</p>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Total expenses</th>
            </tr>
        </thead>
        <tbody>
            <?= $report->getTableContents() ?>
        </tbody>
    </table>
</body>
</html>
