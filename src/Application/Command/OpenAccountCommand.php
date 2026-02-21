<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Application\Command;

use Chip\InterestAccount\Domain\ValueObject\UserId;

final readonly class OpenAccountCommand
{
    public function __construct(
        public UserId $userId,
    ) {
    }
}