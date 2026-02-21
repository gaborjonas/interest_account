<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\EventStore;

use Chip\InterestAccount\Domain\Enum\AccountStatus;
use Chip\InterestAccount\Domain\Event\AccountOpened;
use Chip\InterestAccount\Domain\EventStore\EventStoreInterface;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\InterestRate;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use Chip\InterestAccount\Infrastructure\EventStore\EventStore;
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