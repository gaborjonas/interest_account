<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Exception;

use Chip\InterestAccount\Domain\Exception\AccountClosedException;
use Chip\InterestAccount\Domain\Exception\AccountNotFoundException;
use Chip\InterestAccount\Domain\Exception\InvalidIdException;
use Chip\InterestAccount\Domain\Exception\MalformedUserStatisticsException;
use Chip\InterestAccount\Domain\Exception\UserAlreadyHasAccountException;
use Chip\InterestAccount\Domain\Exception\UserStatisticsException;
use Chip\InterestAccount\Domain\ValueObject\AccountId;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Error;

final class DomainExceptionTest extends TestCase
{
    #[Test]
    public function accountClosedExceptionHasCorrectMessage(): void
    {
        $accountId = AccountId::generate();
        $exception = new AccountClosedException($accountId->value());

        $this->assertSame("Account {$accountId->value()} is closed", $exception->getMessage());
    }

    #[Test]
    public function accountNotFoundExceptionHasCorrectMessage(): void
    {
        $accountId = AccountId::generate();
        $exception = new AccountNotFoundException($accountId->value());

        $this->assertSame("Account with id {$accountId->value()} not found", $exception->getMessage());

    }

    #[Test]
    public function invalidIdExceptionHasCorrectMessage(): void
    {
        $type = 'AccountId';
        $value = 'invalid-uuid';
        $exception = new InvalidIdException($type, $value);

        $this->assertSame("Invalid ID format: $type $value", $exception->getMessage());
    }

    #[Test]
    public function malformedUserStatisticsExceptionHasCorrectMessage(): void
    {
        $exception = new MalformedUserStatisticsException();

        $this->assertSame('Malformed Stats API response', $exception->getMessage());
    }

    #[Test]
    public function userAlreadyHasAccountExceptionHasCorrectMessage(): void
    {
        $userId = UserId::generate();
        $exception = new UserAlreadyHasAccountException($userId->value());

        $this->assertStringContainsString('User with ID', $exception->getMessage());
        $this->assertStringContainsString('already has an active interest account', $exception->getMessage());
        $this->assertStringContainsString($userId->value(), $exception->getMessage());
    }

    #[Test]
    public function userStatisticsExceptionHasCorrectMessageAndCode(): void
    {
        $message = 'Failed to fetch user stats';
        $code = 500;
        $previous = new RuntimeException('Network error');

        $exception = new UserStatisticsException($message, $code, $previous);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($code, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    #[Test]
    public function userStatisticsExceptionWithDefaults(): void
    {
        $message = Error::class;
        $exception = new UserStatisticsException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertNull($exception->getPrevious());
    }
}
