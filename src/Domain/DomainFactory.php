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

    /** @var TokenSet[] */
    protected $tokenSets = [];

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
            $this->repositoryFactory->getMessageRepository()
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

    public function getTokenSet(PhoneNumber $phoneNumber) : TokenSet
    {
        return $this->tokenSets[$phoneNumber->scalar] ?? $this->tokenSets[$phoneNumber->scalar] =

        new TokenSet($phoneNumber, $this->repositoryFactory->getTokenRepository());
    }
}
