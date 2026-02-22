<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Application\Command\OpenAccount;

use Chip\InterestAccount\Domain\EventStore\EventStoreInterface;
use Chip\InterestAccount\Domain\Exception\UserAlreadyHasAccountException;
use Chip\InterestAccount\Domain\Exception\UserStatisticsException;
use Chip\InterestAccount\Domain\Aggregate\Account;
use Chip\InterestAccount\Domain\Projector\EventProjectorInterface;
use Chip\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use Chip\InterestAccount\Domain\Service\StatsApiClientInterface;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\InterestRate;

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