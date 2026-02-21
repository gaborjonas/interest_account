<?php
declare(strict_types=1);

namespace Chip\InterestAccount\Infrastructure\Projector;
use Chip\InterestAccount\Domain\Event\AccountOpened;
use Chip\InterestAccount\Domain\Event\DomainEvent;
use Chip\InterestAccount\Domain\Projection\Account;
use Chip\InterestAccount\Domain\Projector\EventProjectorInterface;
use Chip\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use RuntimeException;

final readonly class EventProjector implements EventProjectorInterface
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
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
}