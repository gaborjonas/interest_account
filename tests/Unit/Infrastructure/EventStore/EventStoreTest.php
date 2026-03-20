<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\EventStore;

use App\InterestAccount\Domain\Enum\AccountStatus;
use App\InterestAccount\Domain\Event\AccountOpened;
use App\InterestAccount\Domain\EventStore\EventStoreInterface;
use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\InterestRate;
use App\InterestAccount\Domain\ValueObject\UserId;
use App\InterestAccount\Infrastructure\EventStore\EventStore;
use PHPUnit\Framework\TestCase;

final class EventStoreTest extends TestCase
{
    private EventStoreInterface $eventStore;

    private AccountId $accountId;

    private UserId $userId;

    protected function setUp(): void
    {
        $this->eventStore = new EventStore();
        $this->accountId = AccountId::generate();
        $this->userId = UserId::generate();
    }

    public function testSaveAndLoad(): void
    {
        $accountOpened = new AccountOpened($this->accountId, $this->userId, new InterestRate('1.0'), AccountStatus::Open);
        $events = [
            $accountOpened,
        ];
        $this->eventStore->append($this->accountId->value(), $events);

        $retrievedEvents = $this->eventStore->load($this->accountId->value());

        $expected = [$accountOpened];
        $this->assertSame($expected, $retrievedEvents);
    }

    public function testLoadForNonExistentAccount(): void
    {
        $events = $this->eventStore->load($this->accountId->value());
        $this->assertSame([], $events);
    }

}