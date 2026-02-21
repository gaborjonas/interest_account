<?php declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Service;

use Chip\InterestAccount\Domain\Dto\UserStats;
use Chip\InterestAccount\Domain\Exception\MalformedUserStatisticsException;
use Chip\InterestAccount\Domain\Exception\UserStatisticsException;
use Chip\InterestAccount\Domain\Service\StatsApiClientInterface;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use Chip\InterestAccount\Infrastructure\Service\StatsApiClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class StatsApiClientTest extends TestCase
{
    private function createClient(ResponseInterface $response): StatsApiClientInterface
    {
        return new StatsApiClient(new MockHttpClient($response));
    }

    #[Test]
    public function userStatisticsReturnsUserStats(): void
    {
        $userId = UserId::generate();

        $response = new MockResponse(
            body: json_encode(
                [
                    'id' => $userId->value(),
                    'income' => 10000,
                ]
            ),
            info: [
                'http_code' => 200
            ]
        );

        $userStats = $this->createClient($response)->getUserStatistics($userId);

        $this->assertEquals(
            new UserStats($userId->value(), '10000'),
            $userStats
        );
    }

    #[DataProvider('provideUserStatisticsThrowsIfResponseIsMalformedCases')]
    #[Test]
    public function userStatisticsThrowsIfResponseIsMalformed(array $body): void
    {
        $userId = UserId::generate();

        $this->expectExceptionObject(
            new UserStatisticsException(
                $userId->value(),
                previous: new MalformedUserStatisticsException(),
            ),
        );

        $response = new MockResponse(
            body: json_encode($body),
            info: [
                'http_code' => 200
            ]
        );

        $this->createClient($response)->getUserStatistics($userId);
    }

    public static function provideUserStatisticsThrowsIfResponseIsMalformedCases(): iterable
    {
        yield 'ID not set' => [['income' => 100]];
        yield 'Income not set' => [['id' => '123']];
        yield 'ID not string' => [['id' => 123, 'income' => 100]];
        yield 'Income not int' => [['id' => '123', 'income' => '100']];
    }

    #[Test]
    public function userStatisticsThrowsIfResponseIsNon2XX(): void
    {
        $userId = UserId::generate();

        $this->expectExceptionObject(new UserStatisticsException($userId->value(), 500));

        $response = new MockResponse(
            body: 'server error',
            info: [
                'http_code' => 500
            ]
        );

        $this->createClient($response)->getUserStatistics($userId);
    }
}