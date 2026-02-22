<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Projection;

use Chip\InterestAccount\Domain\Enum\TransactionType;
use Chip\InterestAccount\Domain\ValueObject\Money;
use DateTimeImmutable;

final readonly class Transaction
{
    public function __construct(
        public TransactionType $type,
        public Money $amount,
        public DateTimeImmutable $createdAt
    )
    {
    }
}