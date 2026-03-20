<?php

declare(strict_types=1);

namespace App\InterestAccount\Domain\Exception;

final class AccountClosedException extends DomainException
{
    public function __construct(string $accountId)
    {
        parent::__construct("Account $accountId is closed");
    }
}