<?php
declare(strict_types=1);

namespace Tests\Integration;

use Chip\InterestAccount\Application\Command\OpenAccountHandler;
use Chip\InterestAccount\Domain\Enum\AccountStatus;
use Chip\InterestAccount\Domain\ValueObject\InterestRate;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use Chip\InterestAccount\Infrastructure\EventStore\EventStore;
use Chip\InterestAccount\Infrastructure\Projector\EventProjector;
use Chip\InterestAccount\Infrastructure\Repository\AccountRepository;
use Chip\InterestAccount\Infrastructure\Service\StatsApiClient;
use Chip\InterestAccount\InterestAccountService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class InterestAccountServiceTest extends TestCase
{
    #[Test]
    public function first(): void
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
        $eventStore = new EventStore();
        $eventProjector = new EventProjector($accountRepository);

        $service = new InterestAccountService(
            new OpenAccountHandler(
                $accountRepository,
                $stasApiClient,
                $eventStore,
                $eventProjector,
            ),
        );

        $account = $service->openAccount($userId->value());

        $accountProjection = $accountRepository->findByUserId($userId);
        $this->assertNotNull($accountProjection);
        $this->assertEquals($userId, $accountProjection->userId);
        $this->assertSame(AccountStatus::Open, $accountProjection->status);

        $events = $eventStore->load($account->getAggregateId()->value());
        $this->assertCount(1, $events);
        $this->assertEquals($account->getAggregateId(), $events[0]->accountId);
        $this->assertEquals($userId, $events[0]->userId);
        $this->assertEquals(new InterestRate('1.02'), $events[0]->interestRate);
        $this->assertSame(AccountStatus::Open, $events[0]->status);
    }
}