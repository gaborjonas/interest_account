<?php declare(strict_types=1);

namespace App\InterestAccount\Domain\Projector;

use App\InterestAccount\Domain\Event\DomainEvent;

interface EventProjectorInterface
{
    /**
     * @param array<DomainEvent> $events
     */
    public function projectEvents(array $events): void;
}