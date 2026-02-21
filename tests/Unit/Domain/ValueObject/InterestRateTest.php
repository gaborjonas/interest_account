<?php
declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObject;

use Chip\InterestAccount\Domain\ValueObject\InterestRate;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

final class InterestRateTest extends TestCase
{
    #[DataProvider('provideFromMonthlyIncomeCalculatesCorrectRateCases')]
    #[Test]
    public function fromMonthlyIncomeCalculatesCorrectRate(string $expectedRate, ?string $monthlyIncome): void
    {
        $interestRate = InterestRate::fromMonthlyIncome($monthlyIncome);

        $this->assertSame($expectedRate, $interestRate->value());
    }

    /**
     * @return iterable<string, array{0: numeric-string, 1: ?string}>
     */
    public static function provideFromMonthlyIncomeCalculatesCorrectRateCases(): iterable
    {

        yield 'Unknown income' => ['0.5', null];
        yield 'Low income' => ['0.93', '4999'];
        yield 'High income edge case' => ['1.02', '5000'];
        yield 'High income' => ['1.02', '5001'];
    }

    #[Test]
    public function negativeRateThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Interest rate cannot be negative');

        new InterestRate('-1.0');
    }

    #[Test]
    public function zeroRateIsAllowed(): void
    {
        $interestRate = new InterestRate('0');
        $this->assertSame('0', $interestRate->value());
    }

    public function testToString(): void
    {
        $rate = new InterestRate('1.02');
        $this->assertSame('1.02', $rate->value());
    }
}