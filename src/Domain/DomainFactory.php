<?php

namespace MrBill\Domain;

use MrBill\Model\Repository\RepositoryFactory;
use MrBill\PhoneNumber;

class DomainFactory
{
    /** @var RepositoryFactory */
    protected $repositoryFactory;

    protected $conversations = [];

    public function __construct(RepositoryFactory $repositoryFactory)
    {
        $this->repositoryFactory = $repositoryFactory;
    }

    public function getConversation(PhoneNumber $phoneNumber) : Conversation
    {
        return $this->conversations[$phoneNumber->scalar] ?? $this->conversations[$phoneNumber->scalar] =

        new Conversation(
            $phoneNumber,
            $this->repositoryFactory->getMessageRepository(),
            $this->repositoryFactory->getTokenRepository()
        );
    }
}
