<?php

declare(strict_types=1);

namespace App\InterestAccount\Application\Query\ListAccountStatement;

use App\InterestAccount\Domain\ValueObject\AccountId;

final readonly class ListAccountStatementQuery
{
    public function __construct(
        public AccountId $accountId
    ) {
    }
}
