<?php
declare(strict_types=1);

namespace Unit\Infrastructure\Projector;

use Chip\InterestAccount\Domain\Event\DomainEvent;
use Chip\InterestAccount\Infrastructure\Projector\EventProjector;
use Chip\InterestAccount\Infrastructure\Repository\AccountRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class EventProjectorTest extends TestCase
{
    #[Test]
    public function unknownEventIsNotProjected(): void
    {
        $unknownEvent = new readonly class extends DomainEvent {};

        $this->expectExceptionObject(new RuntimeException('Unknown event type: ' . $unknownEvent::class));

        $projector = new EventProjector(new AccountRepository());

        $projector->projectEvents([$unknownEvent]);

    }
}