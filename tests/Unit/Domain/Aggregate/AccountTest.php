<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Aggregate;

use App\InterestAccount\Domain\Aggregate\Account;
use App\InterestAccount\Domain\Enum\AccountStatus;
use App\InterestAccount\Domain\Event\AccountOpened;
use App\InterestAccount\Domain\Event\DepositMade;
use App\InterestAccount\Domain\Event\DomainEvent;
use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\InterestRate;
use App\InterestAccount\Domain\ValueObject\Money;
use App\InterestAccount\Domain\ValueObject\UserId;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class AccountTest extends TestCase
{
    private AccountId $accountId;
    private UserId $userId;

    protected function setUp(): void
    {
        $this->accountId = AccountId::generate();
        $this->userId = UserId::generate();
    }

    public function testOpenAccount(): void
    {
        $interestRate = new InterestRate('1.0');

        $account = Account::open(
            accountId: $this->accountId,
            userId: $this->userId,
            interestRate: $interestRate
        );

        $this->assertSame($this->accountId->value(), $account->getAggregateId()->value());
        $this->assertSame($this->userId->value(), $account->getUserId()->value());
        $this->assertTrue($account->getBalance()->isZero());
        $this->assertSame('1.0', $account->getInterestRate()->value());

        $events = $account->pullEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(AccountOpened::class, $events[0]);
    }

    public function testReplayEvents(): void
    {
        $account = Account::open(
            accountId: $this->accountId,
            userId: $this->userId,
            interestRate: new InterestRate('1.0')
        );
        $account->deposit(Money::fromString('101.123'));

        $events = $account->pullEvents();

        $this->assertCount(2, $events);
        $this->assertInstanceOf(AccountOpened::class, $events[0]);
        $this->assertInstanceOf(DepositMade::class, $events[1]);

        $replayedAccount = Account::reconstitute($events);

        $this->assertSame($this->accountId->value(), $replayedAccount->getAggregateId()->value());
        $this->assertSame($this->userId->value(), $replayedAccount->getUserId()->value());
        $this->assertSame('1.0', $replayedAccount->getInterestRate()->value());
        $this->assertSame('101.123', $replayedAccount->getBalance()->value());
        $this->assertSame(AccountStatus::Open, $replayedAccount->getStatus());
        $this->assertSame(
            new DateTimeImmutable('now')->format('Y-m-d H:i'),
            $replayedAccount->getOpenedAt()->format('Y-m-d H:i'),
        );
    }

    #[Test]
    public function replayEventsThrowsIfEventIsUnknown(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown event type: App\InterestAccount\Domain\Event\DomainEvent@anonymous');

        $unknownEvent = new readonly class extends DomainEvent {
            public function getEventName(): string
            {
                return 'unknown';
            }
        };

        $events = [$unknownEvent];
        Account::reconstitute($events);
    }
}