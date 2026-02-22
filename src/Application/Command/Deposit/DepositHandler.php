<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Application\Command\Deposit;

use Chip\InterestAccount\Domain\Aggregate\Account;
use Chip\InterestAccount\Domain\Enum\AccountStatus;
use Chip\InterestAccount\Domain\EventStore\EventStoreInterface;
use Chip\InterestAccount\Domain\Exception\AccountClosedException;
use Chip\InterestAccount\Domain\Exception\AccountNotFoundException;
use Chip\InterestAccount\Domain\Exception\InvalidDepositException;
use Chip\InterestAccount\Domain\Projector\EventProjectorInterface;
use Chip\InterestAccount\Domain\Repository\AccountRepositoryInterface;

readonly class DepositHandler
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private EventStoreInterface $eventStore,
        private EventProjectorInterface $eventProjector,
    )
    {
    }

    /**
     * @throws AccountNotFoundException
     * @throws InvalidDepositException
     * @throws AccountClosedException
     */
    public function handle(DepositCommand $command): Account
    {
        $accountProjection = $this->accountRepository->findById($command->accountId);

        if ($accountProjection === null) {
            throw new AccountNotFoundException($command->accountId->value());
        }

        if ($accountProjection->userId->equals($command->userId) === false) {
            throw new InvalidDepositException(
                "User {$command->userId->value()} is not the owner of account {$accountProjection->accountId->value()}",
            );
        }

        if ($accountProjection->status === AccountStatus::Closed) {
            throw new AccountClosedException($accountProjection->accountId->value());
        }

        $eventStream = $this->eventStore->load($command->accountId->value());

        $account = Account::reconstitute($eventStream);
        $account->deposit($command->amount);

        $events = $account->pullEvents();
        $this->eventStore->append($account->getAggregateId()->value(), $events);
        $this->eventProjector->projectEvents($events);

        return $account;
    }
}
