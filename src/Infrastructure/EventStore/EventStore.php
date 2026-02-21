<?php declare(strict_types=1);

namespace Chip\InterestAccount\Infrastructure\EventStore;

use Chip\InterestAccount\Domain\Event\DomainEvent;
use Chip\InterestAccount\Domain\EventStore\EventStoreInterface;

final class EventStore implements EventStoreInterface
{

    /**
     * @var array<string, list<DomainEvent>>
     */
    private array $events = [];

    public function load(string $aggregateId): array
    {
        return $this->events[$aggregateId] ?? [];
    }

    public function append(string $aggregateId, array $events): void
    {
        foreach ($events as $event) {
            $this->events[$aggregateId][] = $event;
        }
    }
}