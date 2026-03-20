<?php
declare(strict_types=1);

namespace Tests\Integration\InterestAccount;

use App\InterestAccount\Application\Command\CalculateInterest\CalculateInterestHandler;
use App\InterestAccount\Application\Command\Deposit\DepositHandler;
use App\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use App\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementHandler;
use App\InterestAccount\Domain\Enum\AccountStatus;
use App\InterestAccount\Domain\Enum\TransactionType;
use App\InterestAccount\Domain\Event\AccountOpened;
use App\InterestAccount\Domain\Event\DepositMade;
use App\InterestAccount\Domain\ValueObject\InterestRate;
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

final class DepositTest extends TestCase
{
    #[Test]
    public function accountOpened(): void
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
            $clock,
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

        $this->assertSame('133.333333', $account->getBalance()->value());

        $events = $eventStore->load($account->getAggregateId()->value());

        $this->assertCount(3, $events);
        $this->assertInstanceOf(AccountOpened::class, $events[0]);
        $this->assertEquals($account->getAggregateId(), $events[0]->accountId);
        $this->assertEquals($userId, $events[0]->userId);
        $this->assertEquals(new InterestRate('1.02'), $events[0]->interestRate);
        $this->assertSame(AccountStatus::Open, $events[0]->status);

        $this->assertInstanceOf(DepositMade::class, $events[1]);
        $this->assertEquals($account->getAggregateId(), $events[1]->accountId);
        $this->assertEquals('100', $events[1]->amount->value());

        $this->assertInstanceOf(DepositMade::class, $events[2]);
        $this->assertEquals($account->getAggregateId(), $events[2]->accountId);
        $this->assertEquals('33.333333', $events[2]->amount->value());

        $transactions = $transactionRepository->findByAccountId($account->getAggregateId());

        $this->assertCount(2, $transactions);
        $this->assertSame(TransactionType::Deposit, $transactions[0]->type);
        $this->assertSame('100', $transactions[0]->amount->value());

        $this->assertSame(TransactionType::Deposit, $transactions[1]->type);
        $this->assertSame('33.333333', $transactions[1]->amount->value());
    }
}