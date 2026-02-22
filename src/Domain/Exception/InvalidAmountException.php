<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Exception;

class InvalidAmountException extends DomainException
{
    public function __construct(string $message = 'Invalid amount provided')
    {
        parent::__construct($message);
    }
}
