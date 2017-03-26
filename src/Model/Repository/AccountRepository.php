<?php

namespace MrBill\Model\Repository;

use MrBill\Model\Account;
use MrBill\PhoneNumber;

class AccountRepository extends Repository
{
    public function createNewAccount() : Account
    {
        $id = $this->dataStore->scalarIncrement($this->getAccountIdKey());

        $newAccount = new Account($id, []);

        $this->storeAccount($newAccount);

        return $newAccount;
    }

    protected function storeAccount(Account $account) : void
    {
        $this->dataStore->mapPutItem($this->getAccountsKey(), $account->id, json_encode($account->toMap()));
    }

    public function getAccountIfExists(int $id) : ?Account
    {
        $data = $this->dataStore->mapGetItem($this->getAccountsKey(), $id);
        if ($data === null)
            return null;

        return Account::createFromMap(json_decode($data, true));
    }

    public function getAccountByPhoneIfExists(PhoneNumber $phone) : ?Account
    {
        $mapKey = $this->getPhoneToAccountMapKey();
        $accountId = $this->dataStore->mapGetItem($mapKey, $phone->scalar);

        if ($accountId === null) {
            return null;
        }
        return $this->getAccountIfExists((int) $accountId);
    }

    public function updateAccount(Account $account) : void
    {
        $existingAccount = $this->getAccountIfExists($account->id);
        if (!$existingAccount)
            throw new \Exception();

        $this->storeAccount($account);

        $mapKey = $this->getPhoneToAccountMapKey();
        foreach ($this->getRemovedPhones($existingAccount, $account) as $phone)
            $this->dataStore->mapRemoveItem($mapKey, $phone->scalar);
        foreach ($this->getAddedPhones($existingAccount, $account) as $phone)
            $this->dataStore->mapPutItem($mapKey, $phone->scalar, $account->id);
    }

    protected function getRemovedPhones(Account $oldAccount, Account $newAccount) : \Generator
    {
        $map = [];
        foreach ($newAccount->phones as $phone)
            $map[$phone->scalar] = true;

        foreach ($oldAccount->phones as $phone)
            if (!array_key_exists($phone->scalar, $map))
                yield $phone;
    }

    protected function getAddedPhones(Account $oldAccount, Account $newAccount) : \Generator
    {
        return $this->getRemovedPhones($newAccount, $oldAccount);
    }

    protected function getAccountIdKey() : string
    {
        return 'account:id';
    }

    protected function getAccountsKey() : string
    {
        return 'accounts';
    }

    protected function getPhoneToAccountMapKey() : string
    {
        return 'account:phoneMap';
    }
}
