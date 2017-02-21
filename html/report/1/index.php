<?php

use MrBill\Apps\Report\Report1;
use MrBill\Domain\DomainFactory;
use MrBill\Model\Repository\RepositoryFactory;
use MrBill\Persistence\DataStore;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$factory = new DomainFactory(new RepositoryFactory(new DataStore()));
$report = new Report1($factory, $_GET);

if ($report->hasInitializationError()) {
    http_response_code(400);
    exit;
}

?><!DOCTYPE html>
<html>
<head>
    <title>Your Expenses &mdash; Mr. Bill</title>
</head>
<body style="max-width: 800px">
    <h1>Your Expenses</h1>

    <h2><?= $report->getDateText() ?></h2>

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
