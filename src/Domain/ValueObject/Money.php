<?php declare(strict_types=1);

namespace Chip\InterestAccount\Domain\ValueObject;

use BcMath\Number;
use InvalidArgumentException;
use ValueError;

final readonly class Money
{
    private Number $value;

    /**
     * @param numeric-string $value
     */
    private function __construct(string $value)
    {
        try {
            $this->value = new Number($value);
        } catch (ValueError) {
            throw new InvalidArgumentException('Amount must be a valid number');
        }

        if ($this->value->compare(new Number('0')->value) < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    /**
     * @param numeric-string $value
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public static function zero(): self
    {
        return new self('0');
    }

    public function isZero(): bool
    {
        return $this->value->compare(new Number('0')->value) === 0;
    }

    public function value(): string
    {
        return (string) $this->value;
    }
}