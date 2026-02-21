<?php declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Projector;

use Chip\InterestAccount\Domain\Event\DomainEvent;

interface EventProjectorInterface
{
    /**
     * @param array<DomainEvent> $events
     */
    public function projectEvents(array $events): void;
}