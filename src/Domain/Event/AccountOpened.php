<?php declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Event;

use Chip\InterestAccount\Domain\Enum\AccountStatus;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\InterestRate;
use Chip\InterestAccount\Domain\ValueObject\UserId;

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