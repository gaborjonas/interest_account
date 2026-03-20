<?php

declare(strict_types=1);

namespace App\InterestAccount\Domain\ValueObject;

use App\InterestAccount\Domain\Exception\InvalidIdException;
use Symfony\Component\Uid\Uuid;

abstract readonly class Id
{
    protected Uuid $value;

    final protected function __construct(Uuid $uuid)
    {
        $this->value = $uuid;
    }

    /**
     * @throws InvalidIdException
     */
    public static function fromString(string $value): static
    {
        if (!Uuid::isValid($value)) {
            throw new InvalidIdException(static::class, $value);
        }

        return new static(Uuid::fromString($value));
    }

    public static function generate(): static
    {
        return new static(Uuid::v4());
    }

    public function value(): string
    {
        return $this->value->toString();
    }
}