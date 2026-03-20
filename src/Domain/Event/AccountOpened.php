<?php declare(strict_types=1);

namespace App\InterestAccount\Domain\Event;

use App\InterestAccount\Domain\Enum\AccountStatus;
use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\InterestRate;
use App\InterestAccount\Domain\ValueObject\UserId;

final readonly class AccountOpened extends DomainEvent
{
    public function __construct(
        public AccountId $accountId,
        public UserId $userId,
        public InterestRate $interestRate,
        public AccountStatus $status
    ) {
        parent::__construct();
    }
}