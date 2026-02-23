<?php

declare(strict_types=1);

namespace Chip\InterestAccount;

use Chip\InterestAccount\Domain\Aggregate\Account;
use Chip\InterestAccount\Domain\Exception\DomainException;
use Chip\InterestAccount\Domain\Projection\Transaction;
use Chip\InterestAccount\Domain\ValueObject\Money;

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