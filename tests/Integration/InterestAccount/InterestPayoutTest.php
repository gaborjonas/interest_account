<?php

declare(strict_types=1);

namespace Tests\Integration\InterestAccount;

use Chip\InterestAccount\Application\Command\CalculateInterest\CalculateInterestHandler;
use Chip\InterestAccount\Application\Command\Deposit\DepositHandler;
use Chip\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use Chip\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementHandler;
use Chip\InterestAccount\Domain\Enum\TransactionType;
use Chip\InterestAccount\Domain\Event\AccountOpened;
use Chip\InterestAccount\Domain\Event\DepositMade;
use Chip\InterestAccount\Domain\Event\InterestCalculated;
use Chip\InterestAccount\Domain\Event\InterestPaid;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use Chip\InterestAccount\Infrastructure\EventStore\EventStore;
use Chip\InterestAccount\Infrastructure\Projector\EventProjector;
use Chip\InterestAccount\Infrastructure\Repository\AccountRepository;
use Chip\InterestAccount\Infrastructure\Repository\TransactionRepository;
use Chip\InterestAccount\Infrastructure\Service\StatsApiClient;
use Chip\InterestAccount\InterestAccountService;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class InterestPayoutTest extends TestCase
{
    #[Test]
    public function interestPayoutAfterThreeDays(): void
    {
        $userId = UserId::generate();
        $statsApiClient = new StatsApiClient(
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
                $statsApiClient,
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

        // Open account and deposit money
        $account = $service->openAccount($userId->value());
        $account = $service->deposit(
            accountId: $account->getAggregateId()->value(),
            userId: $userId->value(),
            amount: '1000',
        );

        // Calculate interest after 3 days (should trigger payout)
        $threeDaysLater = new DateTimeImmutable()->modify('+3 days');
        $result = $service->calculateInterest($account->getAggregateId()->value(), $threeDaysLater);

        $this->assertArrayHasKey('account', $result);
        $this->assertArrayHasKey('payoutAmount', $result['interestCalculation']);
        $this->assertArrayHasKey('pendingAmount', $result['interestCalculation']);
        
        // Check that balance increased by interest payout
        $this->assertSame('1000.08', $result['account']->getBalance()->value());

        // Verify events
        $events = $eventStore->load($account->getAggregateId()->value());
        $this->assertCount(4, $events);
        
        $this->assertInstanceOf(AccountOpened::class, $events[0]);
        $this->assertInstanceOf(DepositMade::class, $events[1]);
        $this->assertInstanceOf(InterestPaid::class, $events[2]);
        $this->assertInstanceOf(InterestCalculated::class, $events[3]);

        // Verify interest paid event
        $this->assertEquals($account->getAggregateId(), $events[2]->accountId);
        $this->assertSame('0.08', $events[2]->amount->value());

        // Verify interest calculated event
        $this->assertEquals($account->getAggregateId(), $events[3]->accountId);
        $this->assertSame('0.0038379427', $events[3]->pendingInterest->value());

        // Verify transactions
        $transactions = $transactionRepository->findByAccountId($account->getAggregateId());
        $this->assertCount(2, $transactions);
        
        $this->assertSame(TransactionType::Deposit, $transactions[0]->type);
        $this->assertSame('1000', $transactions[0]->amount->value());
        
        $this->assertSame(TransactionType::InterestPayout, $transactions[1]->type);
        $this->assertSame('0.08', $transactions[1]->amount->value());
    }

    #[Test]
    public function noInterestPayoutBeforeThreeDays(): void
    {
        $userId = UserId::generate();
        $statsApiClient = new StatsApiClient(
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
                $statsApiClient,
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

        // Open account and deposit money
        $account = $service->openAccount($userId->value());
        $account = $service->deposit(
            accountId: $account->getAggregateId()->value(),
            userId: $userId->value(),
            amount: '1000',
        );

        // Try to calculate interest immediately (should not trigger payout)
        $result = $service->calculateInterest($account->getAggregateId()->value());

        $this->assertArrayHasKey('account', $result);
        $this->assertArrayHasKey('interestCalculation', $result);
        $this->assertNull($result['interestCalculation']);
        
        // Balance should remain unchanged
        $this->assertSame('1000', $result['account']->getBalance()->value());

        // Verify only account opened and deposit events exist
        $events = $eventStore->load($result['account']->getAggregateId()->value());
        $this->assertCount(2, $events);
        
        $this->assertInstanceOf(AccountOpened::class, $events[0]);
        $this->assertInstanceOf(DepositMade::class, $events[1]);

        // Verify only deposit transaction exists
        $transactions = $transactionRepository->findByAccountId($result['account']->getAggregateId());
        $this->assertCount(1, $transactions);
        $this->assertSame(TransactionType::Deposit, $transactions[0]->type);
    }

    #[Test]
    public function multipleInterestPayoutsOverTime(): void
    {
        $userId = UserId::generate();
        $statsApiClient = new StatsApiClient(
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
                $statsApiClient,
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

        // Open account and deposit money
        $account = $service->openAccount($userId->value());
        $account = $service->deposit(
            accountId: $account->getAggregateId()->value(),
            userId: $userId->value(),
            amount: '1000',
        );

        // First interest calculation after 3 days
        $threeDaysLater = new DateTimeImmutable('now')->modify('+3 days');
        $result1 = $service->calculateInterest($account->getAggregateId()->value(), $threeDaysLater);
        $this->assertArrayHasKey('account', $result1);
        $this->assertArrayHasKey('payoutAmount', $result1['interestCalculation']);
        $this->assertArrayHasKey('pendingAmount', $result1['interestCalculation']);
        
        // Second interest calculation after another 3 days
        $sixDaysLater = new DateTimeImmutable('now')->modify('+6 days');
        $result2 = $service->calculateInterest($account->getAggregateId()->value(), $sixDaysLater);
        $this->assertArrayHasKey('account', $result2);
        $this->assertArrayHasKey('payoutAmount', $result2['interestCalculation']);
        $this->assertArrayHasKey('pendingAmount', $result2['interestCalculation']);

        // Verify multiple interest payout events
        $events = $eventStore->load($account->getAggregateId()->value());
        $this->assertCount(6, $events);
        
        $interestPaidEvents = array_filter($events, fn($e) => $e instanceof InterestPaid);
        $this->assertCount(2, $interestPaidEvents);

        // Verify multiple interest payout transactions
        $transactions = $transactionRepository->findByAccountId($account->getAggregateId());
        $interestPayoutTransactions = array_filter($transactions, fn($t) => $t->type === TransactionType::InterestPayout);
        $this->assertCount(2, $interestPayoutTransactions);
    }
}
