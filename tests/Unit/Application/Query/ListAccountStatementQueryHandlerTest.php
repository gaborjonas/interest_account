<?php

declare(strict_types=1);

namespace Unit\Application\Query;

use Chip\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementHandler;
use Chip\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementQuery;
use Chip\InterestAccount\Domain\Exception\AccountNotFoundException;
use Chip\InterestAccount\Domain\Repository\AccountRepositoryInterface;
use Chip\InterestAccount\Domain\Repository\TransactionRepositoryInterface;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

final class ListAccountStatementQueryHandlerTest extends TestCase
{
    private AccountRepositoryInterface&MockObject $accountRepository;
    private Stub&TransactionRepositoryInterface $transactionRepository;

    private ListAccountStatementHandler $handler;
    protected function setUp(): void
    {
        $this->accountRepository = $this->createMock(AccountRepositoryInterface::class);
        $this->transactionRepository = $this->createStub(TransactionRepositoryInterface::class);

        $this->handler = new ListAccountStatementHandler(
            $this->accountRepository,
            $this->transactionRepository,
        );
    }

    #[Test]
    public function failsIfAccountIsNotFound(): void
    {
        $accountId = AccountId::generate();

        $this->expectExceptionObject(new AccountNotFoundException($accountId->value()));

        $this->handler->handle(new ListAccountStatementQuery($accountId));

    }
}