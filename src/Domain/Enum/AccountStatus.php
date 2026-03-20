<?php

declare(strict_types=1);

namespace App\InterestAccount\Domain\Enum;

enum AccountStatus
{
    case Open;
    case Closed;
}
