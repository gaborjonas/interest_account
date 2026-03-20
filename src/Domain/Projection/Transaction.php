<?php

declare(strict_types=1);

namespace App\InterestAccount\Domain\Projection;

use App\InterestAccount\Domain\Enum\TransactionType;
use App\InterestAccount\Domain\ValueObject\Money;
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