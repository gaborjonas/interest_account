<?php declare(strict_types=1);

namespace Chip\InterestAccount\Infrastructure\Repository;

use Chip\InterestAccount\Domain\Projection\Transaction;
use Chip\InterestAccount\Domain\Repository\TransactionRepositoryInterface;
use Chip\InterestAccount\Domain\ValueObject\AccountId;

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