<?php declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Aggregate;

use Chip\InterestAccount\Domain\Event\DomainEvent;
use Chip\InterestAccount\Domain\ValueObject\Id;

abstract class AggregateRoot
{
    /**
     * @var array<DomainEvent>
     */
    private array $events = [];

    abstract protected function __construct();

    /**
     * @return array<DomainEvent>
     */
    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    protected function record(DomainEvent $event): void
    {
        $this->events[] = $event;
        $this->apply($event);
    }

    abstract protected function apply(DomainEvent $event): void;

    /**
     * @param array<DomainEvent> $events
     */
    public static function reconstitute(array $events): static
    {
        $aggregate = new static();
        foreach ($events as $event) {
            $aggregate->apply($event);
        }

        return $aggregate;
    }

    abstract public function getAggregateId(): Id;
}