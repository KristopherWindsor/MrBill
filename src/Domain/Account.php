<?php

namespace MrBill\Domain;

use MrBill\Model\Repository\AccountRepository;
use MrBill\PhoneNumber;
use MrBill\Model\Account as AccountModel;

class Account
{
    /** @var AccountModel */
    protected $account;

    /** @var AccountRepository */
    protected $accountRepository;

    public function __construct(AccountModel $account, AccountRepository $accountRepository)
    {
        $this->account = $account;
        $this->accountRepository = $accountRepository;
    }

    public static function getByIDIfExists(int $accountId, AccountRepository $accountRepository) : ?Account
    {
        $account = $accountRepository->getAccountIfExists($accountId);

        if (!$account)
            return null;

        return new static($account, $accountRepository);
    }

    public static function getOrCreateForPhoneNumber(
        PhoneNumber $phoneNumber,
        AccountRepository $accountRepository
    ) : Account {
        $account = $accountRepository->getAccountByPhoneIfExists($phoneNumber);

        if (!$account) {
            $account = $accountRepository->createNewAccount();
            $account->phones[] = $phoneNumber;
            $accountRepository->updateAccount($account);
        }

        return new static($account, $accountRepository);
    }

    public function getByID() : int
    {
        return $this->account->id;
    }
}
