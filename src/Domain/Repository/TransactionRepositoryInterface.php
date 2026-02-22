<?php declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Repository;

use Chip\InterestAccount\Domain\Projection\Transaction;
use Chip\InterestAccount\Domain\ValueObject\AccountId;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction, AccountId $accountId): void;

    /**
     * @return list<Transaction>
     */
    public function findByAccountId(AccountId $accountId): array;
}