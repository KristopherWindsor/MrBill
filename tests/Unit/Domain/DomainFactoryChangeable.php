<?php

namespace MrBillTest\Unit\Domain;

use MrBill\Domain\Conversation;
use MrBill\Domain\DomainFactory;
use MrBill\Domain\ExpenseSet;
use MrBill\Domain\TokenSet;

class DomainFactoryChangeable extends DomainFactory
{
    /** @var Conversation[] */
    public $conversations = [];

    /** @var ExpenseSet[] */
    public $expenseSets = [];

    /** @var TokenSet[] */
    public $tokenSets = [];
}
