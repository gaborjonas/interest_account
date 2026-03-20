<?php
declare(strict_types=1);

namespace App\InterestAccount\Domain\Event;

use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\Money;

final readonly class InterestCalculated extends DomainEvent
{
    public function __construct(
        public AccountId $accountId,
        public Money $interest,
        public Money $pendingInterest,
    ) {
        parent::__construct();
    }
}