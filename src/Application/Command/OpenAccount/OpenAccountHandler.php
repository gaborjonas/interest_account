<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Application\Command\OpenAccount;

use Chip\InterestAccount\Domain\Exception\UserAlreadyHasAccountException;
use Chip\InterestAccount\Domain\Exception\UserStatisticsException;
use Chip\InterestAccount\Domain\Aggregate\Account;
use Chip\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\InterestRate;
use Chip\InterestAccount\Infrastructure\EventStore\EventStore;
use Chip\InterestAccount\Infrastructure\Projector\EventProjector;
use Chip\InterestAccount\Infrastructure\Service\StatsApiClient;

class OpenAccountHandler
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private StatsApiClient $statsApiClient,
        private EventStore $eventStore,
        private EventProjector $eventProjector,
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