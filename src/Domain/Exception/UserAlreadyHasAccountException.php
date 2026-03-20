<?php

declare(strict_types=1);

namespace App\InterestAccount\Domain\Exception;

final class UserAlreadyHasAccountException extends DomainException
{
    public function __construct(string $userId)
    {
        parent::__construct("User with ID $userId already has an active interest account");
    }
}