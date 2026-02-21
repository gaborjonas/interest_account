<?php declare(strict_types=1);

namespace Chip\InterestAccount\Domain\ValueObject;

use BcMath\Number;
use InvalidArgumentException;

final readonly class InterestRate
{
    private const string DEFAULT_INTEREST_RATE = '0.5';

    private const string STANDARD_INTEREST_RATE = '0.93';

    private const string PREMIUM_INTEREST_RATE = '1.02';

    private const string INCOME_THRESHOLD = '5000';

    private Number $value;

    /**
     * @param numeric-string $rate
     */
    public function __construct(string $rate)
    {
        $this->value = new Number($rate);

        if ($this->value->compare(new Number('0')->value) < 0) {
            throw new InvalidArgumentException('Interest rate cannot be negative');
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
     * @return numeric-string
     */
    public function value(): string
    {
        return $this->value->value;
    }
}