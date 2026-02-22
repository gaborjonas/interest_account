<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Enum;

enum AccountStatus
{
    case Open;
    case Closed;
}
