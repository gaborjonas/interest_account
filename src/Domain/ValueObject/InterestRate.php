<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Domain\ValueObject;

use BcMath\Number;
use Chip\InterestAccount\Domain\Exception\InvalidInterestRateException;
use RoundingMode;

final readonly class InterestRate
{
    private const int CALCULATION_SCALE = 10;
    private const string PAYOUT_THRESHOLD = '0.01';

    private const string DEFAULT_INTEREST_RATE = '0.5';

    private const string STANDARD_INTEREST_RATE = '0.93';

    private const string PREMIUM_INTEREST_RATE = '1.02';

    private const string INCOME_THRESHOLD = '5000';

    private const string DAYS_IN_YEAR = '365';

    private Number $value;

    /**
     * @param numeric-string $rate
     * @throws InvalidInterestRateException
     */
    public function __construct(string $rate)
    {
        $this->value = new Number($rate);

        if ($this->value->compare(new Number('0')->value) < 0) {
            throw new InvalidInterestRateException('Interest rate cannot be negative');
        }
    }

    /**
     * @param ?numeric-string $monthlyIncome
     */
    public static function fromMonthlyIncome(?string $monthlyIncome): self
    {
        if ($monthlyIncome === null) {
            return new self(self::DEFAULT_INTEREST_RATE);
        }

        $monthlyIncome = new Number($monthlyIncome);
        $incomeThreshold = new Number(self::INCOME_THRESHOLD);

        if ($monthlyIncome->compare($incomeThreshold) === -1) {
            return new self(self::STANDARD_INTEREST_RATE);
        }

        return new self(self::PREMIUM_INTEREST_RATE);
    }

    /**
     * @param numeric-string $days
     * @return array{
     *     payoutAmount: Money,
     *     pendingAmount: Money,
     * }
     */
    public function calculateInterestForAmount(Money $balance, Money $pendingInterest, string $days): array
    {
        $currentBalance = $balance->asNumber();
        $unpaidInterest = $pendingInterest->asNumber();
        $annualRate = $this->value->div('100');

        $days = new Number($days);
        $daysInYear = new Number(self::DAYS_IN_YEAR);
        $payoutThreshold = new Number(self::PAYOUT_THRESHOLD);

        $dailyRate = $annualRate->div($daysInYear, self::CALCULATION_SCALE);
        $newInterest = new Number('0');

        // Calculate interest for each day with compounding
        $daysInt = (int)$days->value;
        for ($day = 0; $day < $daysInt; $day++) {
            $dailyInterest = $currentBalance->mul($dailyRate, self::CALCULATION_SCALE);
            $newInterest = $newInterest->add($dailyInterest, self::CALCULATION_SCALE);
            $currentBalance = $currentBalance->add($dailyInterest, self::CALCULATION_SCALE);
        }

        // Add new interest to any previously accumulated unpaid interest
        $totalCalculatedInterest = $unpaidInterest->add($newInterest, self::CALCULATION_SCALE);

        // Check if we have at least one penny
        if ($totalCalculatedInterest->compare($payoutThreshold) >= 0) {

            // Extract whole pennies for the deposit
            $payOutAmount = $totalCalculatedInterest->round(2, RoundingMode::TowardsZero);

            // Keep the fractional for next time
            $pendingInterest = $totalCalculatedInterest->sub($payOutAmount, self::CALCULATION_SCALE);
        } else {
            // Not enough to pay out, store for next time
            $payOutAmount = new Number('0');
            $pendingInterest = $totalCalculatedInterest;
        }

        return [
            'payoutAmount' => Money::fromString($payOutAmount->value),
            'pendingAmount' => Money::fromString($pendingInterest->value),
        ];
    }

    /**
     * @return numeric-string
     */
    public function value(): string
    {
        return $this->value->value;
    }
}