<?php

namespace MrBill\Domain;

use MrBill\Model\Repository\RepositoryFactory;
use MrBill\PhoneNumber;

class DomainFactory
{
    /** @var RepositoryFactory */
    protected $repositoryFactory;

    /** @var Account[] */
    protected $accounts = [];

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

    public function getAccount(int $accountId) : ?Account
    {
        return $this->accounts[$accountId] ?? $this->accounts[$accountId] =

        Account::getByIDIfExists($accountId, $this->repositoryFactory->getAccountRepository());
    }

    public function getAccountByPhoneNumber(PhoneNumber $phone) : Account
    {
        $account = Account::getOrCreateForPhoneNumber($phone, $this->repositoryFactory->getAccountRepository());

        return $this->accounts[$account->getByID()] ?? $this->accounts[$account->getByID()] = $account;
    }

    public function getConversation(int $accountId, PhoneNumber $phoneNumber) : Conversation
    {
        return $this->conversations[$accountId][$phoneNumber->scalar] ?? (
               $this->conversations[$accountId][$phoneNumber->scalar] =

        new Conversation(
            $accountId,
            $phoneNumber,
            $this,
            $this->repositoryFactory->getMessageRepository()
        ));
    }

    public function getExpenseSet(int $accountId) : ExpenseSet
    {
        return $this->expenseSets[$accountId] ?? $this->expenseSets[$accountId] =

        new ExpenseSet(
            $accountId,
            $this->repositoryFactory->getExpenseRepository()
        );
    }

    public function getTokenSet(int $accountId) : TokenSet
    {
        return $this->tokenSets[$accountId] ?? $this->tokenSets[$accountId] =

        new TokenSet($accountId, $this->repositoryFactory->getTokenRepository());
    }
}
