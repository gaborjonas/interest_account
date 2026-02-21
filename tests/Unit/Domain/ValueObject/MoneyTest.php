<?php declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObject;

use Chip\InterestAccount\Domain\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    #[Test]
    public function zeroCreatedCorrectly(): void
    {
        $money = Money::zero();
        $this->assertSame('0', $money->value());
        $this->assertTrue($money->isZero());
    }

    #[Test]
    public function toStringReturnsCorrectValue(): void
    {
        $money = Money::fromString('10000');
        $this->assertSame('10000', $money->value());
    }

    #[Test]
    public function nonNumericValueThrowsException(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Amount must be a valid number'));
        Money::fromString('a');
    }

    #[Test]
    public function negativeValueThrowsException(): void
    {
        $this->expectExceptionObject(new InvalidArgumentException('Amount cannot be negative'));
        Money::fromString('-1');
    }
}