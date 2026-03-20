<?php
declare(strict_types=1);

namespace Tests\Integration\InterestAccount;

use App\InterestAccount\Application\Command\CalculateInterest\CalculateInterestHandler;
use App\InterestAccount\Application\Command\Deposit\DepositHandler;
use App\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use App\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementHandler;
use App\InterestAccount\Domain\Enum\AccountStatus;
use App\InterestAccount\Domain\Event\AccountOpened;
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

final class OpenInterestAccountTest extends TestCase
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
            $clock
        );

        $account = $service->openAccount($userId->value());

        $accountProjection = $accountRepository->findByUserId($userId);
        $this->assertNotNull($accountProjection);
        $this->assertEquals($userId, $accountProjection->userId);
        $this->assertSame(AccountStatus::Open, $accountProjection->status);

        $events = $eventStore->load($account->getAggregateId()->value());

        $this->assertCount(1, $events);
        $this->assertInstanceOf(AccountOpened::class, $events[0]);
        $this->assertEquals($account->getAggregateId(), $events[0]->accountId);
        $this->assertEquals($userId, $events[0]->userId);
        $this->assertEquals(new InterestRate('1.02'), $events[0]->interestRate);
        $this->assertSame(AccountStatus::Open, $events[0]->status);
    }
}