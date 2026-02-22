<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Application\Command\Deposit;

use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\Money;
use Chip\InterestAccount\Domain\ValueObject\UserId;

final readonly class DepositCommand
{
    public function __construct(
        public AccountId $accountId,
        public UserId $userId,
        public Money $amount
    ) {
    }
}
