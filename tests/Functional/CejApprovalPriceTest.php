<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Participant\Guest\GuestRepository;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantService;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\User\UserStatus;
use LogicException;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class CejApprovalPriceTest extends AppTestCase
{
    public function testTeamContingentIstIsApprovedStraightToPaid(): void
    {
        $app = $this->getTestApp();
        $ist = $this->createCejIst($app, 'detail.contingent.team');

        $this->getService($app, ParticipantService::class)->approveRegistration($ist);

        $reloadedIst = $this->getService($app, IstRepository::class)->get($ist->id);
        self::assertSame(UserStatus::Paid, $reloadedIst->getUserButNotNull()->status);
        self::assertSame([], $this->getService($app, PaymentRepository::class)->findByParticipant($reloadedIst));
    }

    public function testCzechiaContingentIstGetsCzechPayment(): void
    {
        $app = $this->getTestApp();
        $ist = $this->createCejIst($app, 'detail.contingent.czechia');

        $this->getService($app, ParticipantService::class)->approveRegistration($ist);

        $reloadedIst = $this->getService($app, IstRepository::class)->get($ist->id);
        self::assertSame(UserStatus::Approved, $reloadedIst->getUserButNotNull()->status);
        self::assertSame(
            ['3300 CZK'],
            array_map(
                static fn (Payment $payment): string => $payment->price . ' ' . $payment->currency,
                $this->getService($app, PaymentRepository::class)->findByParticipant($reloadedIst),
            ),
        );
    }

    public function testApprovingRoleWithoutCejPriceKeepsUserStatusUnchanged(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $event = $this->getTypedTestEvent($app, 'cej', 'cej-approval-guest');
        $this->initializeMailerSettings($app, $event);
        $guest = $this->createParticipantWithRole($container, 'guest', $event);
        $statusBefore = $guest->getUserButNotNull()->status;

        try {
            $this->getService($app, ParticipantService::class)->approveRegistration($guest);
            self::fail('Expected LogicException was not thrown');
        } catch (LogicException) {
        }

        $reloadedGuest = $this->getService($app, GuestRepository::class)->get($guest->id);
        self::assertSame($statusBefore, $reloadedGuest->getUserButNotNull()->status);
        self::assertNotSame(UserStatus::Approved, $reloadedGuest->getUserButNotNull()->status);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createCejIst(App $app, string $contingent): Ist
    {
        $container = $app->getContainer();
        $event = $this->getTypedTestEvent($app, 'cej', 'cej-approval');
        $this->mutateEventForTest($container, $event, ['defaultPrice' => 1000]);
        $this->initializeMailerSettings($app, $event);

        $participant = $this->createParticipantWithRole($container, 'ist', $event);
        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Cej';
        $ist->lastName = 'Approval';
        $ist->nickname = 'CA';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $ist->getUserButNotNull()->email;
        $ist->gender = 'man';
        $ist->country = 'detail.countryCzechRepublic';
        $ist->contingent = $contingent;
        $istRepository->persist($ist);

        return $ist;
    }
}
