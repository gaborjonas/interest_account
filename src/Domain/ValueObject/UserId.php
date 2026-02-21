<?php
declare(strict_types=1);

namespace Chip\InterestAccount\Domain\ValueObject;

final readonly class UserId extends Id
{
    public function equals(UserId $userId): bool
    {
        return $this->value->equals($userId->value);
    }
}