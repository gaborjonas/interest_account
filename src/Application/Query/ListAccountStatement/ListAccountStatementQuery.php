<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Application\Query\ListAccountStatement;

use Chip\InterestAccount\Domain\ValueObject\AccountId;

final readonly class ListAccountStatementQuery
{
    public function __construct(
        public AccountId $accountId
    ) {
    }
}
