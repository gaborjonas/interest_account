<?php

declare(strict_types=1);

namespace Chip\InterestAccount;

use Chip\InterestAccount\Application\Command\OpenAccountCommand;
use Chip\InterestAccount\Application\Command\OpenAccountHandler;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use Chip\InterestAccount\Domain\Aggregate\Account;

final readonly class InterestAccountService implements InterestAccountServiceInterface
{
    public function __construct(
        private OpenAccountHandler $openAccountHandler,
    )
    {
    }

    public function openAccount(string $userId): Account
    {
        $command = new OpenAccountCommand(UserId::fromString($userId));

        return $this->openAccountHandler->handle($command);
    }
}