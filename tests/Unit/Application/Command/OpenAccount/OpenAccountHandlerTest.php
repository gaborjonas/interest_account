<?php

declare(strict_types=1);

namespace Unit\Application\Command\OpenAccount;

use App\InterestAccount\Application\Command\OpenAccount\OpenAccountCommand;
use App\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use App\InterestAccount\Domain\Enum\AccountStatus;
use App\InterestAccount\Domain\EventStore\EventStoreInterface;
use App\InterestAccount\Domain\Exception\UserAlreadyHasAccountException;
use App\InterestAccount\Domain\Projection\Account;
use App\InterestAccount\Domain\Projector\EventProjectorInterface;
use App\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use App\InterestAccount\Domain\Service\StatsApiClientInterface;
use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\UserId;
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