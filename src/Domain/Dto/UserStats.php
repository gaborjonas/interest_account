<?php
declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Dto;

final readonly class UserStats
{
    /**
     * @param numeric-string $income
     */
    public function __construct(
        public string $id,
        public string $income,
    )
    {
    }
}