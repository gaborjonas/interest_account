<?php

declare(strict_types=1);

namespace Chip\InterestAccount;

use Chip\InterestAccount\Application\Command\Deposit\DepositCommand;
use Chip\InterestAccount\Application\Command\Deposit\DepositHandler;
use Chip\InterestAccount\Application\Command\OpenAccount\OpenAccountCommand;
use Chip\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use Chip\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementHandler;
use Chip\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementQuery;
use Chip\InterestAccount\Domain\Exception\DomainException;
use Chip\InterestAccount\Domain\Projection\Transaction;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\Money;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use Chip\InterestAccount\Domain\Aggregate\Account;

final readonly class InterestAccountService implements InterestAccountServiceInterface
{
    public function __construct(
        private OpenAccountHandler $openAccountHandler,
        private DepositHandler $depositHandler,
        private ListAccountStatementHandler $listAccountStatementHandler,
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
}