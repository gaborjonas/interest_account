<?php declare(strict_types=1);

namespace Chip\InterestAccount\Domain\EventStore;

use Chip\InterestAccount\Domain\Event\DomainEvent;

interface EventStoreInterface
{
    /**
     * @param array<DomainEvent> $events
     */
    public function append(string $aggregateId, array $events): void;

    /**
     * @return array<DomainEvent>
     */
    public function load(string $aggregateId): array;
}