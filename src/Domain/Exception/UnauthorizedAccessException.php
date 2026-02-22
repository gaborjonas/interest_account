<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Exception;

class UnauthorizedAccessException extends DomainException
{
    public function __construct(string $message = 'Unauthorized access to account')
    {
        parent::__construct($message);
    }
}
