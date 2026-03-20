<?php

declare(strict_types=1);

namespace App\InterestAccount;

use App\InterestAccount\Domain\Aggregate\Account;
use App\InterestAccount\Domain\Exception\DomainException;
use App\InterestAccount\Domain\Projection\Transaction;
use App\InterestAccount\Domain\ValueObject\Money;

interface InterestAccountServiceInterface
{
    /**
     * @throws DomainException
     */
    public function openAccount(string $userId): Account;

    /**
     * @param numeric-string $amount
     * @throws DomainException
     */
    public function deposit(string $accountId, string $userId, string $amount): Account;

    /**
     * @return list<Transaction>
     * @throws DomainException
     */
    public function listAccountStatement(string $accountId): array;

    /**
     * @return array{
     *     account: Account,
     *     interestCalculation: ?array{
     *       payoutAmount: Money,
     *       pendingAmount: Money,
     *   }
     * }
     * @throws DomainException
     */
    public function calculateInterest(string $accountId): array;
}