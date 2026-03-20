<?php
declare(strict_types=1);

namespace App\InterestAccount\Domain\Projection;

use App\InterestAccount\Domain\Enum\AccountStatus;
use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\UserId;

final class Account
{
    public function __construct(
        public AccountId $accountId,
        public UserId $userId,
        public AccountStatus $status,
    )
    {
    }
}