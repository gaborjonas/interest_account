<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Event;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

abstract readonly class DomainEvent
{
    private Uuid $eventId;

    private DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->eventId = Uuid::v4();
        $this->occurredAt = new DateTimeImmutable();
    }

    public function getEventId(): Uuid
    {
        return $this->eventId;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}