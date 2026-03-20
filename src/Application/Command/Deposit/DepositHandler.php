<?php

declare(strict_types=1);

namespace App\InterestAccount\Application\Command\Deposit;

use App\InterestAccount\Domain\Aggregate\Account;
use App\InterestAccount\Domain\Enum\AccountStatus;
use App\InterestAccount\Domain\EventStore\EventStoreInterface;
use App\InterestAccount\Domain\Exception\AccountClosedException;
use App\InterestAccount\Domain\Exception\AccountNotFoundException;
use App\InterestAccount\Domain\Exception\UnauthorizedAccessException;
use App\InterestAccount\Domain\Projector\EventProjectorInterface;
use App\InterestAccount\Domain\Repository\AccountRepositoryInterface;

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
     * @throws AccountClosedException
     * @throws UnauthorizedAccessException
     */
    public function handle(DepositCommand $command): Account
    {
        $accountProjection = $this->accountRepository->findById($command->accountId);

        if ($accountProjection === null) {
            throw new AccountNotFoundException($command->accountId->value());
        }

        if ($accountProjection->userId->equals($command->userId) === false) {
            throw new UnauthorizedAccessException(
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
