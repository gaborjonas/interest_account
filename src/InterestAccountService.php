<?php

declare(strict_types=1);

namespace App\InterestAccount;

use App\InterestAccount\Application\Command\CalculateInterest\CalculateInterestCommand;
use App\InterestAccount\Application\Command\CalculateInterest\CalculateInterestHandler;
use App\InterestAccount\Application\Command\Deposit\DepositCommand;
use App\InterestAccount\Application\Command\Deposit\DepositHandler;
use App\InterestAccount\Application\Command\OpenAccount\OpenAccountCommand;
use App\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use App\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementHandler;
use App\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementQuery;
use App\InterestAccount\Domain\Exception\DomainException;
use App\InterestAccount\Domain\Projection\Transaction;
use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\Money;
use App\InterestAccount\Domain\ValueObject\UserId;
use App\InterestAccount\Domain\Aggregate\Account;
use Psr\Clock\ClockInterface;

final readonly class InterestAccountService implements InterestAccountServiceInterface
{
    public function __construct(
        private OpenAccountHandler $openAccountHandler,
        private DepositHandler $depositHandler,
        private ListAccountStatementHandler $listAccountStatementHandler,
        private CalculateInterestHandler $calculateInterestHandler,
        private ClockInterface $clock
    )
    {
    }

    /**
     * @throws DomainException
     */
    public function openAccount(string $userId): Account
    {
        $command = new OpenAccountCommand(UserId::fromString($userId));

        return $this->openAccountHandler->handle($command);
    }

    /**
     * @throws DomainException
     */
    public function deposit(string $accountId, string $userId, string $amount): Account
    {
         $command = new DepositCommand(
            AccountId::fromString($accountId),
            UserId::fromString($userId),
            Money::fromString($amount),
        );

        return $this->depositHandler->handle($command);
    }

    /**
     * @return list<Transaction>
     * @throws DomainException
     */
    public function listAccountStatement(string $accountId): array
    {
        $query = new ListAccountStatementQuery(AccountId::fromString($accountId));

        return $this->listAccountStatementHandler->handle($query);
    }

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
    public function calculateInterest(string $accountId): array
    {
        $command = new CalculateInterestCommand(
            AccountId::fromString($accountId),
            $this->clock->now(),
        );

        return $this->calculateInterestHandler->handle($command);
    }
}