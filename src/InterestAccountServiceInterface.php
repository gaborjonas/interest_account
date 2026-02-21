<?php

declare(strict_types=1);

namespace Chip\InterestAccount;

use Chip\InterestAccount\Domain\Aggregate\Account;

interface InterestAccountServiceInterface
{
    public function openAccount(string $userId): Account;
}