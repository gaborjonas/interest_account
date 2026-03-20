<?php

declare(strict_types=1);

namespace App\InterestAccount\Application\Command\OpenAccount;

use App\InterestAccount\Domain\EventStore\EventStoreInterface;
use App\InterestAccount\Domain\Exception\UserAlreadyHasAccountException;
use App\InterestAccount\Domain\Exception\UserStatisticsException;
use App\InterestAccount\Domain\Aggregate\Account;
use App\InterestAccount\Domain\Projector\EventProjectorInterface;
use App\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use App\InterestAccount\Domain\Service\StatsApiClientInterface;
use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\InterestRate;

class OpenAccountHandler
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private StatsApiClientInterface $statsApiClient,
        private EventStoreInterface $eventStore,
        private EventProjectorInterface $eventProjector,
    ) {
    }

    /**
     * @throws UserAlreadyHasAccountException|UserStatisticsException
     */
    public function handle(OpenAccountCommand $command): Account
    {
        if ($this->accountRepository->findByUserId($command->userId) !== null) {
            throw new UserAlreadyHasAccountException($command->userId->value());
        }

        $userStats = $this->statsApiClient->getUserStatistics($command->userId);
        $interestRate = InterestRate::fromMonthlyIncome($userStats->income);

        $account = Account::open(
            accountId: AccountId::generate(),
            userId: $command->userId,
            interestRate: $interestRate
        );

        $events = $account->pullEvents();

        $this->eventStore->append($account->getAggregateId()->value(), $events);
        $this->eventProjector->projectEvents($events);

        return $account;
    }
}