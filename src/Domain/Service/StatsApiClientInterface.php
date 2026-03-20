<?php declare(strict_types=1);

namespace App\InterestAccount\Domain\Service;

use App\InterestAccount\Domain\Dto\UserStats;
use App\InterestAccount\Domain\Exception\UserStatisticsException;
use App\InterestAccount\Domain\ValueObject\UserId;

interface StatsApiClientInterface
{
    /**
     * @throws UserStatisticsException
     */
    public function getUserStatistics(UserId $userId): UserStats;
}