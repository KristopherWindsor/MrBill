<?php

namespace MrBill\Domain;

use MrBill\Model\Repository\RepositoryFactory;
use MrBill\PhoneNumber;

class DomainFactory
{
    /** @var RepositoryFactory */
    protected $repositoryFactory;

    /** @var Conversation[] */
    protected $conversations = [];

    /** @var ExpenseSet[] */
    protected $expenseSets = [];

    public function __construct(RepositoryFactory $repositoryFactory)
    {
        $this->repositoryFactory = $repositoryFactory;
    }

    public function getConversation(PhoneNumber $phoneNumber) : Conversation
    {
        return $this->conversations[$phoneNumber->scalar] ?? $this->conversations[$phoneNumber->scalar] =

        new Conversation(
            $phoneNumber,
            $this,
            $this->repositoryFactory->getMessageRepository(),
            $this->repositoryFactory->getTokenRepository()
        );
    }

    public function getExpenseSet(PhoneNumber $phoneNumber) : ExpenseSet
    {
        return $this->expenseSets[$phoneNumber->scalar] ?? $this->expenseSets[$phoneNumber->scalar] =

        new ExpenseSet(
            $phoneNumber,
            $this->repositoryFactory->getExpenseRepository()
        );
    }
}
