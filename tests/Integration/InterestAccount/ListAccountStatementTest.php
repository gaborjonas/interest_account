<?php

declare(strict_types=1);

namespace Tests\Integration\InterestAccount;

use App\InterestAccount\Application\Command\CalculateInterest\CalculateInterestHandler;
use App\InterestAccount\Application\Command\Deposit\DepositHandler;
use App\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use App\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementHandler;
use App\InterestAccount\Domain\Enum\AccountStatus;
use App\InterestAccount\Domain\Enum\TransactionType;
use App\InterestAccount\Domain\ValueObject\UserId;
use App\InterestAccount\Infrastructure\EventStore\EventStore;
use App\InterestAccount\Infrastructure\Projector\EventProjector;
use App\InterestAccount\Infrastructure\Repository\AccountRepository;
use App\InterestAccount\Infrastructure\Repository\TransactionRepository;
use App\InterestAccount\Infrastructure\Service\StatsApiClient;
use App\InterestAccount\InterestAccountService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
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
                'https://stats.dev.App.test/'
            ),
        );
        $accountRepository = new AccountRepository();
        $transactionRepository = new TransactionRepository();
        $eventStore = new EventStore();
        $eventProjector = new EventProjector(
            $accountRepository,
            $transactionRepository,
        );
        $clock = new MockClock();

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
            $clock
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