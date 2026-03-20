<?php

declare(strict_types=1);

namespace App\InterestAccount\Application\Command\CalculateInterest;

use App\InterestAccount\Domain\Aggregate\Account;
use App\InterestAccount\Domain\EventStore\EventStoreInterface;
use App\InterestAccount\Domain\Exception\AccountNotFoundException;
use App\InterestAccount\Domain\Projector\EventProjectorInterface;
use App\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use App\InterestAccount\Domain\ValueObject\Money;

readonly class CalculateInterestHandler
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private EventStoreInterface $eventStore,
        private EventProjectorInterface $eventProjector,
    ) {
    }

    /**
     * @return array{
     *     account: Account,
     *     interestCalculation: ?array{
     *       payoutAmount: Money,
     *       pendingAmount: Money,
     *   }
     * }
     * @throws AccountNotFoundException
     */
    public function handle(CalculateInterestCommand $command): array
    {
        $account = $this->accountRepository->findById($command->accountId);
        
        if ($account === null) {
            throw new AccountNotFoundException($command->accountId->value());
        }

        $eventStream = $this->eventStore->load($command->accountId->value());

        $account = Account::reconstitute($eventStream);
        $interest = $account->calculateInterest($command->calculateAt);

        if ($interest !== null) {
            $events = $account->pullEvents();

            $this->eventStore->append($account->getAggregateId()->value(), $events);
            $this->eventProjector->projectEvents($events);
        }

        return [
            'account' => $account,
            'interestCalculation' => $interest
        ];
    }
}
