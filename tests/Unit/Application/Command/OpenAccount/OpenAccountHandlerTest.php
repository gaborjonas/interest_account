<?php

declare(strict_types=1);

namespace Unit\Application\Command\OpenAccount;

use Chip\InterestAccount\Application\Command\OpenAccount\OpenAccountCommand;
use Chip\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use Chip\InterestAccount\Domain\Enum\AccountStatus;
use Chip\InterestAccount\Domain\EventStore\EventStoreInterface;
use Chip\InterestAccount\Domain\Exception\UserAlreadyHasAccountException;
use Chip\InterestAccount\Domain\Projection\Account;
use Chip\InterestAccount\Domain\Projector\EventProjectorInterface;
use Chip\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use Chip\InterestAccount\Domain\Service\StatsApiClientInterface;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class OpenAccountHandlerTest extends TestCase
{
    private AccountRepositoryInterface&MockObject $accountRepository;
    private StatsApiClientInterface&Stub $statsApiClient;
    private EventStoreInterface&Stub $eventStore;
    private EventProjectorInterface&Stub $eventProjector;
    private OpenAccountHandler $handler;

    protected function setUp(): void
    {
        $this->accountRepository = $this->createMock(AccountRepositoryInterface::class);
        $this->statsApiClient = $this->createStub(StatsApiClientInterface::class);
        $this->eventStore = $this->createStub(EventStoreInterface::class);
        $this->eventProjector = $this->createStub(EventProjectorInterface::class);

        $this->handler = new OpenAccountHandler(
          $this->accountRepository,
          $this->statsApiClient,
          $this->eventStore,
          $this->eventProjector,
        );
    }

    #[Test]
    public function failsIfUserAlreadyHasAccount(): void
    {
        $userId = UserId::generate();

        $this->accountRepository
            ->expects($this->once())
            ->method('findByUserId')
            ->with($userId)
            ->willReturn(
                new Account(
                  AccountId::generate(),
                  $userId,
                  AccountStatus::Open,
                ),
            );

        $this->expectExceptionObject(new UserAlreadyHasAccountException($userId->value()));

        $this->handler->handle(new OpenAccountCommand($userId));
    }
}