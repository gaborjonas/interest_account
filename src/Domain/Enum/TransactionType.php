<?php

declare(strict_types=1);

namespace App\InterestAccount\Domain\Enum;

enum TransactionType: string
{
    case Deposit = 'Deposit';
    case InterestPayout = 'Interest Payout';
}
