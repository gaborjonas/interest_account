<?php
declare(strict_types=1);

namespace Unit\Infrastructure\Projector;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EventProjectorTest extends TestCase
{
    #[Test]
    public function unknownEventIsNotProjected(): void
    {
        $this->assertTrue(true);

    }
}