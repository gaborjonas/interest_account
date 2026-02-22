<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Application\Command\CalculateInterest;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use DateTimeImmutable;

final readonly class CalculateInterestCommand
{
    public function __construct(
        public AccountId $accountId,
        public DateTimeImmutable $calculateAt,
    ) {
    }
}
