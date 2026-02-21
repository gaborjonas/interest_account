<?php
declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Projection;

use Chip\InterestAccount\Domain\Enum\AccountStatus;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\UserId;

final readonly class Account
{
    public function __construct(
        public AccountId $accountId,
        public UserId $userId,
        public AccountStatus $status,
    )
    {
    }
}