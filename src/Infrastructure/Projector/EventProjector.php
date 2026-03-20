<?php
declare(strict_types=1);

namespace App\InterestAccount\Infrastructure\Projector;
use App\InterestAccount\Domain\Enum\TransactionType;
use App\InterestAccount\Domain\Event\AccountOpened;
use App\InterestAccount\Domain\Event\DepositMade;
use App\InterestAccount\Domain\Event\DomainEvent;
use App\InterestAccount\Domain\Event\InterestPaid;
use App\InterestAccount\Domain\Projection\Account;
use App\InterestAccount\Domain\Projection\Transaction;
use App\InterestAccount\Domain\Projector\EventProjectorInterface;
use App\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use App\InterestAccount\Domain\Repository\TransactionRepositoryInterface;

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
                InterestPaid::class => $this->projectInterestPaid($event),
                default => null,
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

    private function projectInterestPaid(InterestPaid $event): void
    {
        $transaction = new Transaction(
            type: TransactionType::InterestPayout,
            amount: $event->amount,
            createdAt: $event->getOccurredAt()
        );

        $this->transactionRepository->save($transaction, $event->accountId);
    }
}