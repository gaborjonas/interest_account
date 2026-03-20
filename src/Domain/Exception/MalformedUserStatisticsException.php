<?php

declare(strict_types=1);

namespace App\InterestAccount\Domain\Exception;

final class MalformedUserStatisticsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Malformed Stats API response');
    }
}