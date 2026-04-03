<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\InterestAccount\InterestAccountService;
use App\InterestAccount\Application\Command\CalculateInterest\CalculateInterestHandler;
use App\InterestAccount\Application\Command\Deposit\DepositHandler;
use App\InterestAccount\Application\Command\OpenAccount\OpenAccountHandler;
use App\InterestAccount\Application\Query\ListAccountStatement\ListAccountStatementHandler;
use App\InterestAccount\Infrastructure\EventStore\EventStore;
use App\InterestAccount\Infrastructure\Projector\EventProjector;
use App\InterestAccount\Infrastructure\Repository\AccountRepository;
use App\InterestAccount\Infrastructure\Repository\TransactionRepository;
use App\InterestAccount\Infrastructure\Service\StatsApiClient;
use App\InterestAccount\Domain\ValueObject\UserId;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

$clock = new MockClock();

echo '=== Interest Account Service Test Scenarios ===' . PHP_EOL . PHP_EOL;
echo 'NOTE: Using COMPOUND INTEREST calculation (interest compounds daily)' . PHP_EOL . PHP_EOL;

// Test Scenario 1: £1000 at 1.02% for 3 days (premium rate)
echo 'Scenario 1: £1000 at 1.02% for 3 days (Premium Rate)' . PHP_EOL;
echo '==================================================' . PHP_EOL;

$service = createInterestAccountService(10000, $clock); // £10,000 monthly income = premium rate
$account = $service->openAccount(UserId::generate()->value());
$account = $service->deposit($account->getAggregateId()->value(), $account->getUserId()->value(), '1000');

// Calculate interest after 3 days
$clock->sleep(1);
$clock->modify('+3 days');
$result = $service->calculateInterest($account->getAggregateId()->value());

echo 'Starting balance: £1000' . PHP_EOL;
echo 'Interest rate: 1.02% annually (premium rate)' . PHP_EOL;
echo 'Calculation: Daily compounding (interest adds to principal each day)' . PHP_EOL;
echo 'After 3 days:' . PHP_EOL;
echo '  Final balance: £' . $result['account']->getBalance()->value() . PHP_EOL;
echo '  Interest payout: £' . $result['interestCalculation']['payoutAmount']->value() . PHP_EOL;
echo '  Pending interest: £' . $result['interestCalculation']['pendingAmount']->value() . PHP_EOL;

echo PHP_EOL . PHP_EOL;

// Test Scenario 2: £100 at 0.5% for 3 days (unknown income rate)
echo 'Scenario 2: £100 at 0.5% for 3 days (Unknown Income)' . PHP_EOL;
echo '====================================================' . PHP_EOL;

$service2 = createInterestAccountService(0, $clock); // No income data = default rate
$account2 = $service2->openAccount(UserId::generate()->value());
$account2 = $service2->deposit($account2->getAggregateId()->value(), $account2->getUserId()->value(), '100');

$clock->sleep(1);
$clock->modify('+3 days');
$result2 = $service2->calculateInterest($account2->getAggregateId()->value());

echo 'Starting balance: £100' . PHP_EOL;
echo 'Interest rate: 0.5% annually (default rate)' . PHP_EOL;
echo 'After 3 days:' . PHP_EOL;
echo '  Final balance: £' . $result2['account']->getBalance()->value() . PHP_EOL;
echo '  Interest payout: £' . $result2['interestCalculation']['payoutAmount']->value() . PHP_EOL;
echo '  Pending interest: £' . $result2['interestCalculation']['pendingAmount']->value() . PHP_EOL;
echo '  Should be £0.00 payout (below 1p threshold)' . PHP_EOL;

echo PHP_EOL . PHP_EOL;

echo 'Scenario 3: Multiple Interest Calculations Over Time' . PHP_EOL;
echo '====================================================' . PHP_EOL;

$service3 = createInterestAccountService(3000, $clock); // £3,000 monthly income = standard rate
$account3 = $service3->openAccount(UserId::generate()->value());
$account3 = $service3->deposit($account3->getAggregateId()->value(), $account3->getUserId()->value(), '500');

echo 'Starting balance: £500' . PHP_EOL;
echo 'Interest rate: 0.93% annually (standard rate)' . PHP_EOL;

// First calculation after 3 days
$clock->sleep(1);
$clock->modify('+3 days');
$result3a = $service3->calculateInterest($account3->getAggregateId()->value());

echo PHP_EOL . 'After first 3 days:' . PHP_EOL;
echo '  Balance: £' . $result3a['account']->getBalance()->value() . PHP_EOL;
echo '  Interest payout: £' . $result3a['interestCalculation']['payoutAmount']->value() . PHP_EOL;
echo '  Pending interest: £' . $result3a['interestCalculation']['pendingAmount']->value() . PHP_EOL;

// Second calculation after 6 days total
$clock->sleep(1);
$clock->modify('+6 days');
$result3b = $service3->calculateInterest($account3->getAggregateId()->value());

echo PHP_EOL . 'After 6 days total:' . PHP_EOL;
echo '  Balance: £' . $result3b['account']->getBalance()->value() . PHP_EOL;
echo '  Interest payout: £' . $result3b['interestCalculation']['payoutAmount']->value() . PHP_EOL;
echo '  Pending interest: £' . $result3b['interestCalculation']['pendingAmount']->value() . PHP_EOL;

// Show transaction history
$transactions = $service3->listAccountStatement($account3->getAggregateId()->value());
echo PHP_EOL . 'Transaction history:' . PHP_EOL;
foreach ($transactions as $transaction) {
    echo '  ' . $transaction->type->value . ': £' . $transaction->amount->value() . PHP_EOL;
}

echo PHP_EOL . PHP_EOL;

// Test Scenario 4: Interest rate comparison based on income (with compound interest)
echo 'Scenario 4: Interest Rate Comparison Based on Income' . PHP_EOL;
echo '====================================================' . PHP_EOL;

$incomeScenarios = [
    ['income' => 0, 'rate' => '0.5%', 'description' => 'Unknown income'],
    ['income' => 3000, 'rate' => '0.93%', 'description' => 'Below £5000'],
    ['income' => 10000, 'rate' => '1.02%', 'description' => '£5000 or more'],
];

foreach ($incomeScenarios as $scenario) {
    $service4 = createInterestAccountService($scenario['income'], $clock);
    $account4 = $service4->openAccount(UserId::generate()->value());
    $account4 = $service4->deposit($account4->getAggregateId()->value(), $account4->getUserId()->value(), '1000');

    $clock->sleep(1);
    $clock->modify('+30 days');
    $result4 = $service4->calculateInterest($account4->getAggregateId()->value());

    echo sprintf('%-20s (%s): Payout = £%s, Pending = £%s' . PHP_EOL,
        $scenario['description'],
        $scenario['rate'],
        $result4['interestCalculation']['payoutAmount']->value(),
        $result4['interestCalculation']['pendingAmount']->value()
    );
}

echo PHP_EOL . '=== Test Complete ===' . PHP_EOL;

function createInterestAccountService(int $monthlyIncome, ClockInterface $clock): InterestAccountService
{
    $userId = UserId::generate();
    $statsApiClient = new StatsApiClient(
        new MockHttpClient(
            new MockResponse(
                body: json_encode([
                    'id' => $userId->value(),
                    'income' => $monthlyIncome,
                ]),
                info: [
                    'http_code' => 200,
                ]),
            'https://app.test/'
        ),
    );

    $accountRepository = new AccountRepository();
    $transactionRepository = new TransactionRepository();
    $eventStore = new EventStore();
    $eventProjector = new EventProjector(
        $accountRepository,
        $transactionRepository,
    );

    return new InterestAccountService(
        new OpenAccountHandler(
            $accountRepository,
            $statsApiClient,
            $eventStore,
            $eventProjector,
        ),
        new DepositHandler(
            $accountRepository,
            $eventStore,
            $eventProjector,
        ),
        new ListAccountStatementHandler(
            $accountRepository,
            $transactionRepository,
        ),
        new CalculateInterestHandler(
            $accountRepository,
            $eventStore,
            $eventProjector,
        ),
        $clock,
    );
}