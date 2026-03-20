<?php
declare(strict_types=1);

namespace App\InterestAccount\Domain\ValueObject;

final readonly class AccountId extends Id
{
    public function equals(AccountId $accountId): bool
    {
        return $this->value->equals($accountId->value);
    }
}