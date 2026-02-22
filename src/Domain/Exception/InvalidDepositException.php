<?php
declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Exception;

final class InvalidDepositException extends DomainException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}