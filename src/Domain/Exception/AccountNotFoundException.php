<?php
declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Exception;

final class AccountNotFoundException extends DomainException
{
    public function __construct(string $id)
    {
        parent::__construct("Account with id $id not found");
    }
}