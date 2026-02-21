<?php declare(strict_types=1);

namespace Chip\InterestAccount\Infrastructure\Service;

use Chip\InterestAccount\Domain\Dto\UserStats;
use Chip\InterestAccount\Domain\Exception\MalformedUserStatisticsException;
use Chip\InterestAccount\Domain\Exception\UserStatisticsException;
use Chip\InterestAccount\Domain\Service\StatsApiClientInterface;
use Chip\InterestAccount\Domain\ValueObject\UserId;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final readonly class StatsApiClient implements StatsApiClientInterface
{

    public function __construct(
        private HttpClientInterface $httpClient,
    )
    {
    }

    public function getUserStatistics(UserId $userId): UserStats
    {
        try {
            $response = $this
                ->httpClient
                ->request('GET', "/users/{$userId->value()}")
                ->toArray();

            $this->validateResponse($response);

            return new UserStats($response['id'], (string)$response['income']);

        } catch (Throwable $e) {
            throw new UserStatisticsException(
                "Unable to retrieve user statistics for user {$userId->value()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param array<mixed> $response
     * @phpstan-assert array{id: string, income: int} $response
     * @throws MalformedUserStatisticsException
     */
    private function validateResponse(array $response): void
    {
        if (
            !isset($response['id']) ||
            !isset($response['income']) ||
            !is_string($response['id']) ||
            !is_int($response['income'])
        ) {
            throw new MalformedUserStatisticsException();
        }
    }
}