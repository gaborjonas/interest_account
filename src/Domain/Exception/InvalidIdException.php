<?php

declare(strict_types=1);

namespace App\InterestAccount\Domain\Exception;

final class InvalidIdException extends DomainException
{
    private const string MESSAGE = 'Invalid ID format: %s %s';

    public function __construct(string $type, string $value)
    {
        parent::__construct(sprintf(self::MESSAGE, $type, $value));
    }

}