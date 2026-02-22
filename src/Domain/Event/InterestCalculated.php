<?php
declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Event;

use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\Money;

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