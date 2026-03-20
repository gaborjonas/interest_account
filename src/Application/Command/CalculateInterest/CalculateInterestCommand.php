<?php

declare(strict_types=1);

namespace App\InterestAccount\Application\Command\CalculateInterest;
use App\InterestAccount\Domain\ValueObject\AccountId;
use DateTimeImmutable;

final readonly class CalculateInterestCommand
{
    public function __construct(
        public AccountId $accountId,
        public DateTimeImmutable $calculateAt,
    ) {
    }
}
