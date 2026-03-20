<?php

declare(strict_types=1);

namespace App\InterestAccount\Application\Command\OpenAccount;

use App\InterestAccount\Domain\ValueObject\UserId;

final readonly class OpenAccountCommand
{
    public function __construct(
        public UserId $userId,
    ) {
    }
}