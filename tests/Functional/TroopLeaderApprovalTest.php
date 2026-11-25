<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantException;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class TroopLeaderApprovalTest extends AppTestCase
{
    public function testRefusesTroopLeaderWithClosedTroopParticipant(): void
    {
        $this->assertRefusesTroopLeaderWithParticipantStatus(UserStatus::Closed);
    }

    public function testRefusesTroopLeaderWithOpenTroopParticipant(): void
    {
        $this->assertRefusesTroopLeaderWithParticipantStatus(UserStatus::Open);
    }

    public function testApprovesTroopLeaderWhenParticipantsApprovedPaidOrCancelled(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTroopEvent($app);
        $leader = $this->createClosedTroopLeader($app, $event);
        $this->createTiedTroopParticipant($app, $event, $leader, UserStatus::Approved);
        $this->createTiedTroopParticipant($app, $event, $leader, UserStatus::Paid);
        $this->createTiedTroopParticipant($app, $event, $leader, UserStatus::Cancelled);

        $this->getService($app, ParticipantService::class)
            ->approveRegistration($this->getFreshParticipant($app, $leader));

        self::assertSame('paid', $this->freshStatus($app, $leader));
    }

    public function testApprovesTroopLeaderWithoutParticipants(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTroopEvent($app);
        $leader = $this->createClosedTroopLeader($app, $event);

        $this->getService($app, ParticipantService::class)
            ->approveRegistration($this->getFreshParticipant($app, $leader));

        self::assertSame('paid', $this->freshStatus($app, $leader));
    }

    public function testAjaxApproveRefusesBlockedTroopLeader(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTroopEvent($app);
        $leader = $this->createClosedTroopLeader($app, $event);
        $this->createTiedTroopParticipant($app, $event, $leader, UserStatus::Closed);

        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest(
            '/v3/event/' . $event->slug . '/admin/approving/approveParticipant/' . $leader->id,
            'POST',
        ));

        self::assertSame(
            [
                'translationKey' => 'flash.warning.troopParticipantsNotApproved',
                'translationMessage' => $this->translate($app, 'flash.warning.troopParticipantsNotApproved'),
            ],
            json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR),
        );
        self::assertSame('closed', $this->freshStatus($app, $leader));
    }

    public function testFormApproveFlashesWarningForBlockedTroopLeader(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTroopEvent($app);
        $leader = $this->createClosedTroopLeader($app, $event);
        $this->createTiedTroopParticipant($app, $event, $leader, UserStatus::Open);

        $app = $this->loginAdminForEvent($app, $event);
        $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/admin/approving/approveParticipant/' . $leader->id,
            'POST',
        ));

        self::assertContains(
            $this->translate($app, 'flash.warning.troopParticipantsNotApproved'),
            $this->flashed($app),
        );
        self::assertSame('closed', $this->freshStatus($app, $leader));
    }

    public function testApprovingPageDisablesBlockedTroopLeaderAndListsParticipants(): void
    {
        $suffix = bin2hex(random_bytes(4));
        $app = $this->getTestApp();
        $event = $this->getTroopEvent($app);
        $leader = $this->createClosedTroopLeader($app, $event);
        $this->createTiedTroopParticipant($app, $event, $leader, UserStatus::Closed, 'Blocking' . $suffix);
        $this->createTiedTroopParticipant($app, $event, $leader, UserStatus::Approved, 'Ready' . $suffix);

        $body = $this->getApprovingPageBody($app, $event);

        self::assertStringContainsString($this->translate($app, 'approve-admin.troopParticipantsNotApproved'), $body);
        self::assertStringContainsString(
            'Blocking' . $suffix . ' – ' . $this->translate($app, 'dashboard.userStatus.closed'),
            $body,
        );
        self::assertStringNotContainsString('Ready' . $suffix . ' –', $body);
        self::assertMatchesRegularExpression(
            '/<input type="submit"[^>]*disabled/',
            $this->approveFormOf($body, $leader),
        );
    }

    public function testApprovingPageKeepsButtonEnabledForReadyTroopLeader(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTroopEvent($app);
        $leader = $this->createClosedTroopLeader($app, $event);
        $this->createTiedTroopParticipant($app, $event, $leader, UserStatus::Approved);

        $body = $this->getApprovingPageBody($app, $event);

        self::assertStringNotContainsString(
            $this->translate($app, 'approve-admin.troopParticipantsNotApproved'),
            $body,
        );
        self::assertDoesNotMatchRegularExpression(
            '/<input type="submit"[^>]*disabled/',
            $this->approveFormOf($body, $leader),
        );
    }

    private function assertRefusesTroopLeaderWithParticipantStatus(UserStatus $participantStatus): void
    {
        $app = $this->getTestApp();
        $event = $this->getTroopEvent($app);
        $leader = $this->createClosedTroopLeader($app, $event);
        $this->createTiedTroopParticipant($app, $event, $leader, $participantStatus);

        try {
            $this->getService($app, ParticipantService::class)
                ->approveRegistration($this->getFreshParticipant($app, $leader));
            self::fail('Troop leader with unapproved troop participant was approved');
        } catch (ParticipantException $e) {
            self::assertSame('flash.warning.troopParticipantsNotApproved', $e->translationKey);
        }

        self::assertSame('closed', $this->freshStatus($app, $leader));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getApprovingPageBody(App $app, Event $event): string
    {
        $app = $this->loginAdminForEvent($app, $event);
        $response = $app->handle($this->createRequest('/v2/event/' . $event->slug . '/admin/approving', 'GET'));

        return (string)$response->getBody();
    }

    private function approveFormOf(string $body, Participant $leader): string
    {
        $matched = preg_match(
            '#<form[^>]*approveParticipant/' . $leader->id . '"[^>]*>.*?</form>#s',
            $body,
            $matches,
        );
        self::assertSame(1, $matched);

        return $matches[0];
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getTroopEvent(App $app): Event
    {
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->mutateEventForTest($app->getContainer(), $event, ['allowTroops' => true]);
        $this->initializeMailerSettings($app, $event);

        return $event;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createClosedTroopLeader(App $app, Event $event): Participant
    {
        $leader = $this->createParticipantWithRole($app->getContainer(), 'tl', $event);
        $this->setStatus($app, $leader, UserStatus::Closed);

        return $leader;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createTiedTroopParticipant(
        App $app,
        Event $event,
        Participant $leader,
        UserStatus $status,
        string $lastName = 'Member',
    ): Participant {
        $troopParticipant = $this->createParticipantWithRole($app->getContainer(), 'tp', $event);
        $troopParticipant->lastName = $lastName;
        $this->getService($app, ParticipantRepository::class)->persist($troopParticipant);
        $this->tieToTroop($app, $troopParticipant, $leader);
        $this->setStatus($app, $troopParticipant, $status);

        return $troopParticipant;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function setStatus(App $app, Participant $participant, UserStatus $status): void
    {
        $user = $participant->getUserButNotNull();
        $user->status = $status;
        $this->getService($app, UserRepository::class)->persist($user);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function freshStatus(App $app, Participant $participant): string
    {
        return $this->getService($app, UserRepository::class)
            ->get($participant->getUserButNotNull()->id)
            ->status->value;
    }
}
