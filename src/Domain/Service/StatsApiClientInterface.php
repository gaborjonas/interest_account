<?php declare(strict_types=1);

namespace Chip\InterestAccount\Domain\Service;

use Chip\InterestAccount\Domain\Dto\UserStats;
use Chip\InterestAccount\Domain\Exception\UserStatisticsException;
use Chip\InterestAccount\Domain\ValueObject\UserId;

interface StatsApiClientInterface
{
    /**
     * @throws UserStatisticsException
     */
    public function getUserStatistics(UserId $userId): UserStats;
}