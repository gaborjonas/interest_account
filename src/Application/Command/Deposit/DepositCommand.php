<?php

declare(strict_types=1);

namespace App\InterestAccount\Application\Command\Deposit;

use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\Money;
use App\InterestAccount\Domain\ValueObject\UserId;

final readonly class DepositCommand
{
    public function __construct(
        public AccountId $accountId,
        public UserId $userId,
        public Money $amount
    ) {
    }
}
