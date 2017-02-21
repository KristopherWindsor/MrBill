<?php

namespace MrBill\Model\Repository;

class RepositoryFactory extends Repository
{
    /** @var MessageRepository */
    protected $messageRepository;

    /** @var TokenRepository */
    protected $tokenRepository;

    public function getMessageRepository() : MessageRepository
    {
        return $this->messageRepository ?? $this->messageRepository = new MessageRepository($this->dataStore);
    }

    public function getTokenRepository() : TokenRepository
    {
        return $this->tokenRepository ?? $this->tokenRepository = new TokenRepository($this->dataStore);
    }
}
