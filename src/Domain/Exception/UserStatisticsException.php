<?php declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Exception;

use Throwable;

final class UserStatisticsException extends DomainException
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}