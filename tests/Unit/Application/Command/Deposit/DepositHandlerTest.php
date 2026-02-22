<?php

declare(strict_types=1);

namespace Unit\Application\Command\Deposit;

use Chip\InterestAccount\Application\Command\Deposit\DepositCommand;
use Chip\InterestAccount\Application\Command\Deposit\DepositHandler;
use Chip\InterestAccount\Domain\Enum\AccountStatus;
use Chip\InterestAccount\Domain\Event\AccountOpened;
use Chip\InterestAccount\Domain\EventStore\EventStoreInterface;
use Chip\InterestAccount\Domain\Exception\AccountClosedException;
use Chip\InterestAccount\Domain\Exception\AccountNotFoundException;
use Chip\InterestAccount\Domain\Exception\UnauthorizedAccessException;
use Chip\InterestAccount\Domain\Projection\Account;
use Chip\InterestAccount\Domain\Projector\EventProjectorInterface;
use Chip\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\InterestRate;
use Chip\InterestAccount\Domain\ValueObject\Money;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DepositHandlerTest extends TestCase
{
    private AccountRepositoryInterface&MockObject $accountRepository;
    private EventStoreInterface&MockObject $eventStore;
    private EventProjectorInterface&MockObject $eventProjector;
    private DepositHandler $handler;

    protected function setUp(): void
    {
        $this->accountRepository = $this->createMock(AccountRepositoryInterface::class);
        $this->eventStore = $this->createMock(EventStoreInterface::class);
        $this->eventProjector = $this->createMock(EventProjectorInterface::class);

        $this->handler = new DepositHandler(
            $this->accountRepository,
            $this->eventStore,
            $this->eventProjector,
        );
    }

    #[Test]
    public function failsIfAccountIsNotFound(): void
    {
        $accountId = AccountId::generate();
        $userId = UserId::generate();

        $this->expectExceptionObject(new AccountNotFoundException($accountId->value()));

        $this->accountRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(null);

        $this->handler->handle(
            new DepositCommand(
                $accountId,
                $userId,
                Money::fromString('1234')
            )
        );
    }

    #[Test]
    public function failsIfUserIsNotTheOwnerOfTheAccount(): void
    {
        $accountId = AccountId::generate();
        $ownerUserId = UserId::generate();
        $requesterUserId = UserId::generate();

        $this->expectExceptionObject(
            new UnauthorizedAccessException(
                "User {$requesterUserId->value()} is not the owner of account {$accountId->value()}"
            )
        );

        $this->accountRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(
                new Account(
                    $accountId,
                    $ownerUserId,
                    AccountStatus::Open,
                )
            );

        $this->handler->handle(
            new DepositCommand(
                $accountId,
                $requesterUserId,
                Money::fromString('1234')
            )
        );
    }

    #[Test]
    public function failsIfAccountIsNotOpen(): void
    {
        $accountId = AccountId::generate();
        $userId = UserId::generate();

        $this->expectExceptionObject(new AccountClosedException($accountId->value()));

        $this->accountRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(
                new Account(
                    $accountId,
                    $userId,
                    AccountStatus::Closed,
                )
            );

        $this->handler->handle(
            new DepositCommand(
                $accountId,
                $userId,
                Money::fromString('1234')
            )
        );
    }

    #[Test]
    public function depositMoneyAndCreateReadModel(): void
    {
        $accountId = AccountId::generate();
        $userId = UserId::generate();

        $this->accountRepository
            ->expects($this->once())
            ->method('findById')
            ->willReturn(
                new Account(
                    $accountId,
                    $userId,
                    AccountStatus::Open,
                ),
            );

        $this->eventStore
            ->expects($this->once())
            ->method('load')
            ->with($accountId->value())
            ->willReturn([
                new AccountOpened(
                    $accountId,
                    $userId,
                    InterestRate::fromMonthlyIncome('5000'),
                    AccountStatus::Open,
                )
            ]);

        $this->eventStore
            ->expects($this->once())
            ->method('append');

        $this->eventProjector
            ->expects($this->once())
            ->method('projectEvents');

        $this->handler->handle(
            new DepositCommand(
                $accountId,
                $userId,
                Money::fromString('1234')
            )
        );
    }
}