<?php
declare(strict_types=1);

namespace Chip\InterestAccount\Infrastructure\Projector;
use Chip\InterestAccount\Domain\Enum\TransactionType;
use Chip\InterestAccount\Domain\Event\AccountOpened;
use Chip\InterestAccount\Domain\Event\DepositMade;
use Chip\InterestAccount\Domain\Event\DomainEvent;
use Chip\InterestAccount\Domain\Projection\Account;
use Chip\InterestAccount\Domain\Projection\Transaction;
use Chip\InterestAccount\Domain\Projector\EventProjectorInterface;
use Chip\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use Chip\InterestAccount\Domain\Repository\TransactionRepositoryInterface;
use RuntimeException;

final readonly class EventProjector implements EventProjectorInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private TransactionRepositoryInterface $transactionRepository,
    ) {
    }

    /**
     * @param array<DomainEvent> $events
     */
    public function projectEvents(array $events): void
    {
        foreach ($events as $event) {
            match ($event::class) {
                AccountOpened::class => $this->projectAccountOpened($event),
                DepositMade::class => $this->projectDepositMade($event),
                default => throw new RuntimeException('Unknown event type: ' . $event::class),
            };
        }
    }

    private function projectAccountOpened(AccountOpened $event): void
    {
        $account = new Account(
            accountId: $event->accountId,
            userId: $event->userId,
            status: $event->status,
        );

        $this->accountRepository->save($account);
    }

    private function projectDepositMade(DepositMade $event): void
    {
        $transaction = new Transaction(
            type: TransactionType::Deposit,
            amount: $event->amount,
            createdAt: $event->getOccurredAt()
        );

        $this->transactionRepository->save($transaction, $event->accountId);
    }
}