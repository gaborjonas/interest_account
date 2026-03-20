<?php

declare(strict_types=1);

namespace App\InterestAccount\Application\Query\ListAccountStatement;

use App\InterestAccount\Domain\Exception\AccountNotFoundException;
use App\InterestAccount\Domain\Projection\Transaction;
use App\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use App\InterestAccount\Domain\Repository\TransactionRepositoryInterface;

readonly class ListAccountStatementHandler
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private TransactionRepositoryInterface $transactionRepository
    )
    {
    }

    /**
     * @return list<Transaction>
     * @throws AccountNotFoundException
     */
    public function handle(ListAccountStatementQuery $query): array
    {
        $account = $this->accountRepository->findById($query->accountId);

        if ($account === null) {
            throw new AccountNotFoundException($query->accountId->value());
        }

        return $this->transactionRepository->findByAccountId($query->accountId);
    }
}
