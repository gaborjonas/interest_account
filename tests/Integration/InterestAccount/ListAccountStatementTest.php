<?php

declare(strict_types=1);

namespace Integration\InterestAccount;

use Chip\InterestAccount\Application\Command\CalculateInterest\CalculateInterestHandler;
use Chip\InterestAccount\Application\Command\Deposit\DepositHandler;
use Chip\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use Chip\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementHandler;
use Chip\InterestAccount\Domain\Enum\AccountStatus;
use Chip\InterestAccount\Domain\Enum\TransactionType;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use Chip\InterestAccount\Infrastructure\EventStore\EventStore;
use Chip\InterestAccount\Infrastructure\Projector\EventProjector;
use Chip\InterestAccount\Infrastructure\Repository\AccountRepository;
use Chip\InterestAccount\Infrastructure\Repository\TransactionRepository;
use Chip\InterestAccount\Infrastructure\Service\StatsApiClient;
use Chip\InterestAccount\InterestAccountService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ListAccountStatementTest extends TestCase
{
    #[Test]
    public function listAccountStatement(): void
    {
        $userId = UserId::generate();
        $stasApiClient = new StatsApiClient(
            new MockHttpClient(
                new MockResponse(
                    body: json_encode([
                        'id' => $userId->value(),
                        'income' => 10000,
                    ]),
                    info: [
                        'http_code' => 200,
                    ]),
                'https://stats.dev.chip.test/'
            ),
        );
        $accountRepository = new AccountRepository();
        $transactionRepository = new TransactionRepository();
        $eventStore = new EventStore();
        $eventProjector = new EventProjector(
            $accountRepository,
            $transactionRepository,
        );

        $service = new InterestAccountService(
            new OpenAccountHandler(
                $accountRepository,
                $stasApiClient,
                $eventStore,
                $eventProjector,
            ),
            new DepositHandler(
                $accountRepository,
                $eventStore,
                $eventProjector,
            ),
            new ListAccountStatementHandler(
                $accountRepository,
                $transactionRepository,
            ),
            new CalculateInterestHandler(
                $accountRepository,
                $eventStore,
                $eventProjector,
            ),
        );

        $account = $service->openAccount($userId->value());

        $accountProjection = $accountRepository->findByUserId($userId);
        $this->assertNotNull($accountProjection);
        $this->assertEquals($userId, $accountProjection->userId);
        $this->assertSame(AccountStatus::Open, $accountProjection->status);

        $account = $service->deposit(
            accountId: $account->getAggregateId()->value(),
            userId: $userId->value(),
            amount: '100',
        );

        $account = $service->deposit(
            accountId: $account->getAggregateId()->value(),
            userId: $userId->value(),
            amount: '33.333333',
        );

        $statement = $service->listAccountStatement($account->getAggregateId()->value());

        $this->assertCount(2, $statement);
        $this->assertSame(TransactionType::Deposit, $statement[0]->type);
        $this->assertSame('100', $statement[0]->amount->value());
        $this->assertSame(TransactionType::Deposit, $statement[1]->type);
        $this->assertSame('33.333333', $statement[1]->amount->value());
    }
}