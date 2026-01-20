<?php

namespace Tests\Service\User;

use App\DataFixtures\Factory\EventFactory;
use App\DataFixtures\Factory\LoanFactory;
use App\DataFixtures\Factory\UserFactory;
use App\Service\User\UserHomeDataService;
use Tests\AbstractKernelTestCase;
use Tests\Trait\WithUserSession;

class UserHomeDataServiceTest extends AbstractKernelTestCase
{
    use WithUserSession;

    public function testUserLoanDelayedFalse(): void
    {
        $user = UserFactory::create();
        $openEvent = EventFactory::create(returnDate: null);
        $openLoan = LoanFactory::create(
            startDate: new \DateTime(),
            endDate: null,
            user: $user,
            event: $openEvent,
        );

        $this->loginUser(self::$kernel, $user);

        $this->persistAll($user, $openEvent, $openLoan);

        /** @var UserHomeDataService $test */
        $test = $this->get(UserHomeDataService::class);

        $result = $test->getData();

        $this->assertFalse($result['isDelayed']);
    }

    public function testUserLoanDelayedTrue(): void
    {
        $user = UserFactory::create();
        $closedEvent = EventFactory::create(returnDate: new \DateTime('-1 day'));
        $openLoan = LoanFactory::create(
            startDate: new \DateTime(),
            endDate: null,
            user: $user,
            event: $closedEvent,
        );

        $this->loginUser(self::$kernel, $user);

        $this->persistAll($user, $closedEvent, $openLoan);

        /** @var UserHomeDataService $test */
        $test = $this->get(UserHomeDataService::class);

        $result = $test->getData();

        $this->assertTrue($result['isDelayed']);
    }
}
