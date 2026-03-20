<?php declare(strict_types=1);

namespace App\InterestAccount\Domain\Repository;

use App\InterestAccount\Domain\Projection\Transaction;
use App\InterestAccount\Domain\ValueObject\AccountId;

interface TransactionRepositoryInterface
{
    public function save(Transaction $transaction, AccountId $accountId): void;

    /**
     * @return list<Transaction>
     */
    public function findByAccountId(AccountId $accountId): array;
}