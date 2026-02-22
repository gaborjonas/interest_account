<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Infrastructure\Repository;

use Chip\InterestAccount\Domain\Projection\Account;
use Chip\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\UserId;

final class AccountRepository implements AccountRepositoryInterface
{
    /**
     * @var list<Account>
     */
    private array $accounts = [];
    public function save(Account $account): void
    {
        $this->accounts[] = $account;
    }

    public function findById(AccountId $id): ?Account
    {
        return array_find($this->accounts, function (Account $account) use ($id) {
            return $account->accountId->equals($id);
        });
    }

    public function findByUserId(UserId $userId): ?Account
    {
        return array_find($this->accounts, function (Account $account) use ($userId) {
            return $account->userId->equals($userId);
        });
    }
}