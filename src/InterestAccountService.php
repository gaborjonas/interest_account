<?php

declare(strict_types=1);

namespace Chip\InterestAccount;

use Chip\InterestAccount\Application\Command\Deposit\DepositCommand;
use Chip\InterestAccount\Application\Command\Deposit\DepositHandler;
use Chip\InterestAccount\Application\Command\OpenAccount\OpenAccountCommand;
use Chip\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use Chip\InterestAccount\Domain\Exception\AccountClosedException;
use Chip\InterestAccount\Domain\Exception\AccountNotFoundException;
use Chip\InterestAccount\Domain\Exception\InvalidDepositException;
use Chip\InterestAccount\Domain\Exception\InvalidIdException;
use Chip\InterestAccount\Domain\Exception\UserAlreadyHasAccountException;
use Chip\InterestAccount\Domain\Exception\UserStatisticsException;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\Money;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use Chip\InterestAccount\Domain\Aggregate\Account;

final readonly class InterestAccountService implements InterestAccountServiceInterface
{
    public function __construct(
        private OpenAccountHandler $openAccountHandler,
        private DepositHandler $depositHandler,
    )
    {
    }

    /**
     * @throws UserAlreadyHasAccountException
     * @throws UserStatisticsException
     * @throws InvalidIdException
     */
    public function openAccount(string $userId): Account
    {
        $command = new OpenAccountCommand(UserId::fromString($userId));

        return $this->openAccountHandler->handle($command);
    }

    /**
     * @throws AccountNotFoundException
     * @throws InvalidIdException
     * @throws InvalidDepositException
     * @throws AccountClosedException
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
}