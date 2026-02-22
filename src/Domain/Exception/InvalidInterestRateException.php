<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Exception;

final class InvalidInterestRateException extends DomainException
{
    public function __construct(string $message = 'Invalid interest rate provided')
    {
        parent::__construct($message);
    }
}
