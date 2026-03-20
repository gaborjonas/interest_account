<?php declare(strict_types=1);

namespace App\InterestAccount\Infrastructure\Repository;

use App\InterestAccount\Domain\Projection\Transaction;
use App\InterestAccount\Domain\Repository\TransactionRepositoryInterface;
use App\InterestAccount\Domain\ValueObject\AccountId;

final class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * @var array<string, list<Transaction>>
     */
    private array $transactions = [];

    public function save(Transaction $transaction, AccountId $accountId): void
    {
        $this->transactions[$accountId->value()][] = $transaction;
    }

    public function findByAccountId(AccountId $accountId): array
    {
        return $this->transactions[$accountId->value()] ?? [];
    }
}