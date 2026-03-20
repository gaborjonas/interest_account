<?php
declare(strict_types=1);

namespace Unit\Domain\ValueObject;

use App\InterestAccount\Domain\Exception\InvalidIdException;
use App\InterestAccount\Domain\ValueObject\Id;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class IdTest extends TestCase
{
    #[Test]
    public function createFromNonUuidThrowsException(): void
    {
        $this->expectExceptionObject(new InvalidIdException(Id::class, 'a'));

        Id::fromString('a');
    }
}