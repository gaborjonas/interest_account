<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Repository;

use Chip\InterestAccount\Domain\Projection\Account;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\UserId;

interface AccountRepositoryInterface
{
    public function save(Account $account): void;

    public function findById(AccountId $id): ?Account;

    public function findByUserId(UserId $userId): ?Account;
}