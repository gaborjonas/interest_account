<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Repository;

use App\InterestAccount\Domain\Enum\AccountStatus;
use App\InterestAccount\Domain\Projection\Account;
use App\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use App\InterestAccount\Domain\ValueObject\AccountId;
use App\InterestAccount\Domain\ValueObject\UserId;
use App\InterestAccount\Infrastructure\Repository\AccountRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AccountRepositoryTest extends TestCase
{
    private AccountRepositoryInterface $accountRepository;

    private AccountId $accountId;

    private AccountId $accountId2;

    private UserId $userId;

    private UserId $userId2;

    protected function setUp(): void
    {
        $this->accountRepository = new AccountRepository();
        $this->accountId = AccountId::generate();
        $this->accountId2 = AccountId::generate();
        $this->userId = UserId::generate();
        $this->userId2 = UserId::generate();
    }

    #[Test]
    public function saveAndFindById(): void
    {
        $account = new Account(
            accountId: $this->accountId,
            userId: $this->userId,
            status: AccountStatus::Open,
        );

        $this->accountRepository->save($account);

        $retrievedAccount = $this->accountRepository->findById($this->accountId);

        $this->assertSame($account, $retrievedAccount);
        $this->assertSame($this->accountId->value(), $retrievedAccount->accountId->value());
        $this->assertSame($this->userId->value(), $retrievedAccount->userId->value());
        $this->assertSame(AccountStatus::Open, $retrievedAccount->status);
    }

    #[Test]
    public function findByIdReturnsNullForNonExistentAccount(): void
    {
        $account = $this->accountRepository->findById($this->accountId);
        $this->assertNull($account);
    }

    public function testSaveAndFindByUserId(): void
    {
        $account = new Account(
            accountId: $this->accountId,
            userId: $this->userId,
            status: AccountStatus::Open,
        );

        $this->accountRepository->save($account);

        $retrievedAccount = $this->accountRepository->findByUserId($this->userId);

        $this->assertSame($account, $retrievedAccount);
        $this->assertSame($this->accountId->value(), $retrievedAccount->accountId->value());
        $this->assertSame($this->userId->value(), $retrievedAccount->userId->value());
        $this->assertSame(AccountStatus::Open, $retrievedAccount->status);
    }

    public function testFindByUserIdReturnsNullForNonExistentUser(): void
    {
        $account = $this->accountRepository->findByUserId($this->userId);
        $this->assertNull($account);
    }

    public function testSaveMultipleAccounts(): void
    {
        $account1 = new Account(
            accountId: $this->accountId,
            userId: $this->userId,
            status: AccountStatus::Open,
        );

        $account2 = new Account(
            accountId: $this->accountId2,
            userId: $this->userId2,
            status: AccountStatus::Closed,
        );

        $this->accountRepository->save($account1);
        $this->accountRepository->save($account2);

        $retrievedAccount1 = $this->accountRepository->findById($this->accountId);
        $retrievedAccount2 = $this->accountRepository->findById($this->accountId2);

        $this->assertSame($account1, $retrievedAccount1);
        $this->assertSame($account2, $retrievedAccount2);
        $this->assertSame(AccountStatus::Open, $retrievedAccount1->status);
        $this->assertSame(AccountStatus::Closed, $retrievedAccount2->status);
    }

    public function testFindByUserIdAfterMultipleSaves(): void
    {
        $account1 = new Account(
            accountId: $this->accountId,
            userId: $this->userId,
            status: AccountStatus::Open,
        );

        $account2 = new Account(
            accountId: $this->accountId2,
            userId: $this->userId2,
            status: AccountStatus::Closed,
        );

        $this->accountRepository->save($account1);
        $this->accountRepository->save($account2);

        $retrievedAccount1 = $this->accountRepository->findByUserId($this->userId);
        $retrievedAccount2 = $this->accountRepository->findByUserId($this->userId2);

        $this->assertSame($account1, $retrievedAccount1);
        $this->assertSame($account2, $retrievedAccount2);
        $this->assertSame(AccountStatus::Open, $retrievedAccount1->status);
        $this->assertSame(AccountStatus::Closed, $retrievedAccount2->status);
    }

    public function testFindByIdReturnsCorrectAccountWhenMultipleAccountsExist(): void
    {
        $account1 = new Account(
            accountId: $this->accountId,
            userId: $this->userId,
            status: AccountStatus::Open,
        );

        $account2 = new Account(
            accountId: $this->accountId2,
            userId: $this->userId2,
            status: AccountStatus::Closed,
        );

        $this->accountRepository->save($account1);
        $this->accountRepository->save($account2);

        $foundAccount1 = $this->accountRepository->findById($this->accountId);
        $foundAccount2 = $this->accountRepository->findById($this->accountId2);

        $this->assertSame($account1, $foundAccount1);
        $this->assertSame($account2, $foundAccount2);
        $this->assertNotSame($foundAccount1, $foundAccount2);
    }
}