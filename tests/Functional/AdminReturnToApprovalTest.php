<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantException;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopLeaderRepository;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Payment\PaymentStatus;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class AdminReturnToApprovalTest extends AppTestCase
{
    public function testReturnsApprovedTroopParticipantToClosedAndMailsReason(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $troopParticipant = $this->createApprovedParticipant($app, $event, 'tp');

        $app = $this->loginAdminForEvent($app, $event);
        $sentTo = $this->captureSentMessageRecipients($app);
        $response = $app->handle($this->createRequest(
            $this->returnToApprovalPath($event, $troopParticipant),
            'POST',
            ['reason' => 'wrong troop'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'admin-show-payments', $event), $response->getHeaderLine('Location'));
        self::assertSame('closed', $this->freshUser($app, $troopParticipant)->status->value);
        self::assertSame(1, $this->countRecipient($sentTo, $troopParticipant));
        self::assertContains($this->translate($app, 'flash.success.returnedToApproval'), $this->flashed($app));
    }

    public function testFormPageRendersForApprovedTroopParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $troopParticipant = $this->createApprovedParticipant($app, $event, 'tp');
        $troopParticipant->firstName = 'Returned';
        $troopParticipant->lastName = 'Troopie';
        $this->getService($app, ParticipantRepository::class)->persist($troopParticipant);

        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest($this->returnToApprovalPath($event, $troopParticipant)));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString($this->returnToApprovalPath($event, $troopParticipant), $body);
        self::assertStringContainsString('Returned Troopie', $body);
    }

    public function testPaymentsPageOffersReturnForApprovedTroopParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->mutateEventForTest($app->getContainer(), $event, ['allowTroops' => true]);
        $troopParticipant = $this->createApprovedParticipant($app, $event, 'tp');
        $troopParticipant->firstName = 'Listed';
        $troopParticipant->lastName = 'Troopie';
        $this->getService($app, ParticipantRepository::class)->persist($troopParticipant);

        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/payments'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString($this->returnToApprovalPath($event, $troopParticipant), $body);
        self::assertStringContainsString('Listed Troopie', $body);
        self::assertStringNotContainsString('/payments/addNewPayment/' . $troopParticipant->id, $body);
    }

    public function testSecondSubmitIsRefusedWithoutSecondMail(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $troopParticipant = $this->createApprovedParticipant($app, $event, 'tp');

        $app = $this->loginAdminForEvent($app, $event);
        $sentTo = $this->captureSentMessageRecipients($app);
        $app->handle($this->createRequest(
            $this->returnToApprovalPath($event, $troopParticipant),
            'POST',
            ['reason' => 'wrong troop'],
        ));
        $this->flashed($app);
        $response = $app->handle($this->createRequest(
            $this->returnToApprovalPath($event, $troopParticipant),
            'POST',
            ['reason' => 'wrong troop'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('closed', $this->freshUser($app, $troopParticipant)->status->value);
        self::assertSame(1, $this->countRecipient($sentTo, $troopParticipant));
        self::assertContains($this->translate($app, 'flash.warning.cannotReturnToApproval'), $this->flashed($app));
    }

    public function testRefusesParticipantWithWaitingPayment(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $ist = $this->createApprovedParticipant($app, $event, 'ist');
        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '450', participant: $ist);

        $app = $this->loginAdminForEvent($app, $event);
        $sentTo = $this->captureSentMessageRecipients($app);
        $response = $app->handle($this->createRequest(
            $this->returnToApprovalPath($event, $ist),
            'POST',
            ['reason' => 'wrong troop'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('approved', $this->freshUser($app, $ist)->status->value);
        self::assertSame(0, $this->countRecipient($sentTo, $ist));
        self::assertContains($this->translate($app, 'flash.warning.cannotReturnToApproval'), $this->flashed($app));
    }

    public function testRefusesParticipantWithPaidPayment(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $ist = $this->createApprovedParticipant($app, $event, 'ist');
        $this->createFinancesPayment($app, $event, PaymentStatus::Paid, '450', participant: $ist);

        $app = $this->loginAdminForEvent($app, $event);
        $sentTo = $this->captureSentMessageRecipients($app);
        $response = $app->handle($this->createRequest(
            $this->returnToApprovalPath($event, $ist),
            'POST',
            ['reason' => 'wrong troop'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('approved', $this->freshUser($app, $ist)->status->value);
        self::assertSame(0, $this->countRecipient($sentTo, $ist));
        self::assertContains($this->translate($app, 'flash.warning.cannotReturnToApproval'), $this->flashed($app));
    }

    public function testRefusesStaleParticipantWhoseWaitingPaymentAppearedMeanwhile(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->initializeMailerSettings($app, $event);
        $troopParticipant = $this->createApprovedParticipant($app, $event, 'tp');
        $staleParticipant = $this->getService($app, ParticipantRepository::class)->get($troopParticipant->id);
        self::assertInstanceOf(Participant::class, $staleParticipant);
        self::assertTrue($staleParticipant->canReturnToApproval());

        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '450', participant: $troopParticipant);
        self::assertTrue($staleParticipant->canReturnToApproval());

        $sentTo = $this->captureSentMessageRecipients($app);
        try {
            $this->getService($app, ParticipantService::class)->returnToApproval($staleParticipant, 'wrong troop');
            self::fail('Stale participant with a waiting payment was returned to approval');
        } catch (ParticipantException $e) {
            self::assertSame('flash.warning.cannotReturnToApproval', $e->translationKey);
        }

        self::assertSame('approved', $this->freshUser($app, $troopParticipant)->status->value);
        self::assertSame(0, $this->countRecipient($sentTo, $troopParticipant));
    }

    public function testReturningTroopLeaderClosesItsApprovedTroopParticipants(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $troopLeader = $this->getService($app, TroopLeaderRepository::class)->get(
            $this->createApprovedParticipant($app, $event, 'tl')->id,
        );
        $approvedTroopParticipant = $this->createTroopParticipantTiedTo($app, $event, $troopLeader);
        $approvedTroopParticipantUser = $approvedTroopParticipant->getUserButNotNull();
        $approvedTroopParticipantUser->status = UserStatus::Approved;
        $this->getService($app, UserRepository::class)->persist($approvedTroopParticipantUser);
        $openTroopParticipant = $this->createTroopParticipantTiedTo($app, $event, $troopLeader);

        $app = $this->loginAdminForEvent($app, $event);
        $sentTo = $this->captureSentMessageRecipients($app);
        $response = $app->handle($this->createRequest(
            $this->returnToApprovalPath($event, $troopLeader),
            'POST',
            ['reason' => 'wrong troop'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('closed', $this->freshUser($app, $troopLeader)->status->value);
        self::assertSame('closed', $this->freshUser($app, $approvedTroopParticipant)->status->value);
        self::assertSame('open', $this->freshUser($app, $openTroopParticipant)->status->value);
        self::assertSame(1, $this->countRecipient($sentTo, $troopLeader));
        self::assertSame(0, $this->countRecipient($sentTo, $approvedTroopParticipant));
    }

    public function testFormPageRedirectsWhenParticipantCannotBeReturned(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $ist = $this->createApprovedParticipant($app, $event, 'ist');
        $this->createFinancesPayment($app, $event, PaymentStatus::Waiting, '450', participant: $ist);

        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest($this->returnToApprovalPath($event, $ist)));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'admin-show-payments', $event), $response->getHeaderLine('Location'));
        self::assertContains($this->translate($app, 'flash.warning.cannotReturnToApproval'), $this->flashed($app));
    }

    public function testRefusesPatrolParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $leader = $this->createApprovedParticipant($app, $event, 'pl');
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->patrolLeader = $this->getService($app, PatrolLeaderRepository::class)->get($leader->id);
        $patrolParticipant->firstName = 'Child';
        $patrolParticipant->lastName = 'Patrol';
        $this->getService($app, PatrolParticipantRepository::class)->persist($patrolParticipant);

        $app = $this->loginAdminForEvent($app, $event);
        $sentTo = $this->captureSentMessageRecipients($app);
        $response = $app->handle($this->createRequest(
            $this->returnToApprovalPath($event, $patrolParticipant),
            'POST',
            ['reason' => 'wrong patrol'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('approved', $this->freshUser($app, $leader)->status->value);
        self::assertSame(0, $this->countRecipient($sentTo, $leader));
        self::assertContains($this->translate($app, 'flash.warning.cannotReturnToApproval'), $this->flashed($app));
    }

    public function testContingentAdminCannotReturnOtherContingentParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTypedTestEvent($app, 'cej', 'cej-return-to-approval-scope');
        $ist = $this->createApprovedParticipant($app, $event, 'ist');
        $ist->contingent = EventTypeCej::CONTINGENT_SLOVAKIA;
        $this->getService($app, ParticipantRepository::class)->persist($ist);

        $app = $this->loginAdminForEvent($app, $event, UserRole::ContingentAdminCs);
        $response = $app->handle($this->createRequest($this->returnToApprovalPath($event, $ist)));

        $this->assertRedirectedToAdminDashboard($app, $event, $response);
    }

    public function testContingentAdminCannotSubmitReturnOfOtherContingentParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTypedTestEvent($app, 'cej', 'cej-return-to-approval-post-scope');
        $ist = $this->createApprovedParticipant($app, $event, 'ist');
        $ist->contingent = EventTypeCej::CONTINGENT_SLOVAKIA;
        $this->getService($app, ParticipantRepository::class)->persist($ist);

        $app = $this->loginAdminForEvent($app, $event, UserRole::ContingentAdminCs);
        $response = $app->handle($this->createRequest(
            $this->returnToApprovalPath($event, $ist),
            'POST',
            ['reason' => 'wrong contingent'],
        ));

        $this->assertRedirectedToAdminDashboard($app, $event, $response);
        self::assertSame('approved', $this->freshUser($app, $ist)->status->value);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createApprovedParticipant(App $app, Event $event, string $role): Participant
    {
        $participant = $this->createParticipantWithRole($app->getContainer(), $role, $event);
        $user = $participant->getUserButNotNull();
        $user->status = UserStatus::Approved;
        $this->getService($app, UserRepository::class)->persist($user);

        return $participant;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createTroopParticipantTiedTo(App $app, Event $event, TroopLeader $troopLeader): TroopParticipant
    {
        $troopParticipantRepository = $this->getService($app, TroopParticipantRepository::class);
        $troopParticipant = $troopParticipantRepository->get(
            $this->createParticipantWithRole($app->getContainer(), 'tp', $event)->id,
        );
        $troopParticipant->troopLeader = $troopLeader;
        $troopParticipantRepository->persist($troopParticipant);

        return $troopParticipant;
    }

    private function returnToApprovalPath(Event $event, Participant $participant): string
    {
        return '/v2/event/' . $event->slug . '/admin/payments/returnToApproval/' . $participant->id;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function freshUser(App $app, Participant $participant): User
    {
        return $this->getService($app, UserRepository::class)->get($participant->getUserButNotNull()->id);
    }

    /**
     * @param iterable<string> $recipients
     */
    private function countRecipient(iterable $recipients, Participant $participant): int
    {
        return count(array_filter(
            [...$recipients],
            static fn (string $address): bool => $address === $participant->getUserButNotNull()->email,
        ));
    }
}
