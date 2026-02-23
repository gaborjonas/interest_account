<?php

declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Aggregate;

use Chip\InterestAccount\Domain\Enum\AccountStatus;
use Chip\InterestAccount\Domain\Event\AccountOpened;
use Chip\InterestAccount\Domain\Event\DepositMade;
use Chip\InterestAccount\Domain\Event\DomainEvent;
use Chip\InterestAccount\Domain\Event\InterestCalculated;
use Chip\InterestAccount\Domain\Event\InterestPaid;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\InterestRate;
use Chip\InterestAccount\Domain\ValueObject\Money;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use DateTimeImmutable;
use RuntimeException;

class Account extends AggregateRoot
{
    private const int DAYS_BETWEEN_INTEREST_CALCULATION = 3;
    private AccountId $id;
    private UserId $userId;
    private Money $balance;
    private InterestRate $interestRate;
    private DateTimeImmutable $openedAt;
    private AccountStatus $status;
    private DateTimeImmutable $lastInterestCalculation;
    private Money $pendingInterest;

    protected function __construct()
    {
        $this->balance = Money::zero();
        $this->pendingInterest = Money::zero();
    }

    public static function open(
        AccountId $accountId,
        UserId $userId,
        InterestRate $interestRate
    ): self {
        $account = new self();
        $account->record(
            new AccountOpened(
                accountId: $accountId,
                userId: $userId,
                interestRate: $interestRate,
                status: AccountStatus::Open,
            ),
        );

        return $account;
    }

    public function deposit(Money $amount): void
    {
        $this->record(new DepositMade($this->id, $amount));
    }

    /**
     * @return ?array{
     *      payoutAmount: Money,
     *      pendingAmount: Money,
     *  }
     */
    public function calculateInterest(DateTimeImmutable $calculateAt): ?array
    {
        $daysSinceLastCalculation = $calculateAt->diff($this->lastInterestCalculation)->days;

        if ($daysSinceLastCalculation < self::DAYS_BETWEEN_INTEREST_CALCULATION ||
            $daysSinceLastCalculation % self::DAYS_BETWEEN_INTEREST_CALCULATION !== 0) {
            return null;
        }

        $interest = $this->interestRate->calculateInterestForAmount(
            $this->balance,
            $this->pendingInterest,
            (string)$daysSinceLastCalculation
        );

        if ($interest['payoutAmount']->isZero() === false) {
            $this->record(
                new InterestPaid(
                    accountId: $this->id,
                    amount: $interest['payoutAmount'],
                ));
        }

        $this->record(
            new InterestCalculated(
                accountId: $this->id,
                interest: $interest['payoutAmount'],
                pendingInterest: $interest['pendingAmount'],
            ));

        return $interest;
    }

    protected function apply(DomainEvent $event): void
    {
        match ($event::class) {
            AccountOpened::class => $this->applyAccountOpened($event),
            DepositMade::class => $this->applyDepositMade($event),
            InterestCalculated::class => $this->applyInterestCalculated($event),
            InterestPaid::class => $this->applyInterestPaid($event),
            default => throw new RuntimeException('Unknown event type: ' . $event::class),
        };
    }

    private function applyAccountOpened(AccountOpened $event): void
    {
        $this->id = $event->accountId;
        $this->userId = $event->userId;
        $this->interestRate = $event->interestRate;
        $this->openedAt = $event->getOccurredAt();
        $this->balance = Money::zero();
        $this->status = $event->status;
        $this->lastInterestCalculation = $event->getOccurredAt();
    }

    private function applyDepositMade(DepositMade $event): void
    {
        $this->balance = $this->balance->add($event->amount);
    }

    private function applyInterestCalculated(InterestCalculated $event): void
    {
        $this->lastInterestCalculation = $event->getOccurredAt();
        $this->pendingInterest = $event->pendingInterest;
    }

    private function applyInterestPaid(InterestPaid $event): void
    {
        $this->balance = $this->balance->add($event->amount);
    }

    public function getAggregateId(): AccountId
    {
        return $this->id;
    }

    public function getBalance(): Money
    {
        return $this->balance;
    }

    public function getInterestRate(): InterestRate
    {
        return $this->interestRate;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getStatus(): AccountStatus
    {
        return $this->status;
    }

    public function getOpenedAt(): DateTimeImmutable
    {
        return $this->openedAt;
    }

    public function getPendingInterest(): Money
    {
        return $this->pendingInterest;
    }
}