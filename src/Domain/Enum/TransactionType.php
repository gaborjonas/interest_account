<?php declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Enum;

enum TransactionType
{
    case Deposit;
    case InterestPayout;
}
