<?php

declare(strict_types=1);

namespace App\InterestAccount\Domain\Repository;

use App\InterestAccount\Domain\Projection\Account;
use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\UserId;

interface AccountRepositoryInterface
{
    public function save(Account $account): void;

    public function findById(AccountId $id): ?Account;

    public function findByUserId(UserId $userId): ?Account;
}