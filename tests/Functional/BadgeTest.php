<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\PdfGenerator\PdfGenerator;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class BadgeTest extends AppTestCase
{
    private function makeIst(
        ContainerInterface $container,
        Event $event,
        string $suffix,
        string $nickname,
        UserStatus $status,
    ): void {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var ParticipantService $participantService */
        $participantService = $container->get(ParticipantService::class);
        /** @var UserRepository $userRepository */
        $userRepository = $container->get(UserRepository::class);

        $user = $userService->registerEmailUser('badge-' . $suffix . '@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $participantService->addParamsIntoParticipant($participant, [
            'firstName' => 'First' . $suffix,
            'lastName' => 'Last' . $suffix,
            'nickname' => $nickname,
            'birthDate' => (DateTimeUtils::getDateTime())->format(DATE_ATOM),
            'gender' => 'male',
            'email' => 'badge-' . $suffix . '@example.com',
        ]);

        $user->status = $status;
        $userRepository->persist($user);
    }

    // badge rendering embeds the event logo; a fresh test DB seeds logo_url as '' (unlike real
    // events, which always have one configured), so give it a real file to encode
    private function givePdfRenderedEventARealLogo(EventRepository $eventRepository, Event $event): void
    {
        $event->logoUrl = '/favicon-16x16.png';
        $eventRepository->persist($event);
    }

    public function testBadgeParticipantsIncludeOnlyApprovedAndPaidOrderedByNickname(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);

        $this->makeIst($container, $event, 'paid', 'Zebra', UserStatus::Paid);
        $this->makeIst($container, $event, 'approved', 'Alpha', UserStatus::Approved);
        $this->makeIst($container, $event, 'open', 'OpenNick', UserStatus::Open);

        $repo = $this->getService($app, ParticipantRepository::class);
        $participants = $repo->getParticipantsForBadges($event, [ParticipantRole::Ist]);

        self::assertSame(
            ['Alpha', 'Zebra'],
            array_map(fn (Participant $p) => $p->nickname, $participants),
        );
    }

    public function testBadgeParticipantsAreSortedNaturallyByDisplayedName(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);

        $this->makeIst($container, $event, 'sortZebra', 'ZebraSort', UserStatus::Paid);
        $this->makeIst($container, $event, 'sortLower', 'alphaSort', UserStatus::Paid);
        $this->makeIst($container, $event, 'sortCzech', 'ŠimonSort', UserStatus::Paid);
        $this->makeIst($container, $event, 'sortNoNick', '', UserStatus::Paid);

        $repo = $this->getService($app, ParticipantRepository::class);
        $participants = $repo->getParticipantsForBadges($event, [ParticipantRole::Ist]);

        // badges must come out in human alphabetical order of the name printed on them:
        // case-insensitive, Czech collation (Š between S and T), nickname-less ones by full name —
        // alphaSort < FirstsortNoNick LastsortNoNick < ŠimonSort < ZebraSort
        self::assertSame([
            'badge-sortLower@example.com',
            'badge-sortNoNick@example.com',
            'badge-sortCzech@example.com',
            'badge-sortZebra@example.com',
        ], array_map(fn (Participant $p) => $p->email, $participants));
    }

    public function testBadgesIncludePatrolMemberOfPaidLeaderExactlyOnce(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);
        $event->maximalClosedPatrolsCount = 100;
        $event->maximalPatrolParticipantsCount = 100;
        $eventRepository->persist($event);

        $userService = $this->getService($app, UserService::class);
        $participantService = $this->getService($app, ParticipantService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);

        $plUser = $userService->registerEmailUser('badge-patrol-pl@example.com', $event);
        $plParticipant = $userService->createParticipantSetRole($plUser, 'pl');
        $patrolLeader = $patrolLeaderRepository->get($plParticipant->id);
        $participantService->addParamsIntoParticipant($patrolLeader, [
            'patrolName' => 'BadgePatrol',
            'firstName' => 'Lead',
            'lastName' => 'Er',
            'nickname' => 'PatrolLeaderNick',
            'birthDate' => (DateTimeUtils::getDateTime())->format(DATE_ATOM),
            'gender' => 'male',
            'email' => 'badge-patrol-pl@example.com',
        ]);
        $plUser->status = UserStatus::Paid;
        $userRepository->persist($plUser);

        // patrol member covered by the paid leader — must appear on badges, and only once despite the
        // leader-status expansion in getAllParticipantsWithStatus returning them twice
        $ppUser = $userService->registerEmailUser('badge-patrol-pp@example.com', $event);
        $ppParticipant = $userService->createParticipantSetRole($ppUser, 'pp');
        $patrolParticipant = $patrolParticipantRepository->get($ppParticipant->id);
        $patrolParticipant->patrolLeader = $patrolLeader;
        $participantService->addParamsIntoParticipant($patrolParticipant, [
            'firstName' => 'Patrol',
            'lastName' => 'Member',
            'nickname' => 'PatrolMemberNick',
            'birthDate' => (DateTimeUtils::getDateTime())->format(DATE_ATOM),
            'gender' => 'male',
            'email' => 'badge-patrol-pp@example.com',
        ]);
        $ppUser->status = UserStatus::Paid;
        $userRepository->persist($ppUser);

        $repo = $this->getService($app, ParticipantRepository::class);
        $participants = $repo->getParticipantsForBadges(
            $event,
            [ParticipantRole::PatrolLeader, ParticipantRole::PatrolParticipant],
        );
        $ids = array_map(fn (Participant $p) => $p->id, $participants);

        // the paid leader is included, and the patrol member appears exactly once despite the
        // leader-status expansion returning them twice (dedupe by id)
        self::assertContains($plParticipant->id, $ids);
        self::assertCount(1, array_keys($ids, $ppParticipant->id, true));
    }

    public function testGenerateBadgesProducesPdfContainingNicknameAndQr(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);
        $this->givePdfRenderedEventARealLogo($eventRepository, $event);
        $this->makeIst($container, $event, 'pdf', 'PdfNick', UserStatus::Paid);

        $repo = $this->getService($app, ParticipantRepository::class);
        $participants = $repo->getParticipantsForBadges($event, [ParticipantRole::Ist]);
        $mine = array_slice(
            array_values(array_filter($participants, fn (Participant $p) => $p->nickname === 'PdfNick')),
            0,
            1,
        );
        self::assertCount(1, $mine);

        $pdf = $this->getService($app, PdfGenerator::class);

        $html = $pdf->buildBadgesHtml($event, $mine);
        self::assertStringContainsString('PdfNick', $html); // nickname in the name band
        self::assertStringContainsString('data:image/png;base64', $html); // QR (and logo) embedded
        // the fixed layout renders the name band and the ID/food info row
        self::assertStringContainsString('badge-name-band', $html);
        self::assertStringContainsString('badge-info', $html);
        // the 4x4 crop-frame renders even for a single badge, so a lone badge stays in its inner cell (no stretching)
        self::assertStringContainsString('badge-margin-corner', $html);
        self::assertStringContainsString('badge-margin-top', $html);

        $bytes = $pdf->generateBadges($event, $mine);
        self::assertStringStartsWith('%PDF', $bytes);
    }

    public function testNameBandKeepsSecondLineWhenNicknameIsMissing(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);
        $this->givePdfRenderedEventARealLogo($eventRepository, $event);
        $this->makeIst($container, $event, 'noNick', '', UserStatus::Paid);

        $repo = $this->getService($app, ParticipantRepository::class);
        $participants = $repo->getParticipantsForBadges($event, [ParticipantRole::Ist]);
        $mine = array_slice(
            array_values(array_filter($participants, fn (Participant $p) => $p->firstName === 'FirstnoNick')),
            0,
            1,
        );
        self::assertCount(1, $mine);

        $pdf = $this->getService($app, PdfGenerator::class);

        // without a nickname the full name moves to the big line, but the second line must still
        // render (as &nbsp;) so the colored name band keeps the same height as badges with a nickname
        $html = $pdf->buildBadgesHtml($event, $mine);
        self::assertStringContainsString('class="badge-fullname"', $html);
        self::assertStringContainsString('FirstnoNick LastnoNick', $html);
    }

    public function testGenerateBlankBadgesProducesRequestedCount(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);
        $this->givePdfRenderedEventARealLogo($eventRepository, $event);

        $pdf = $this->getService($app, PdfGenerator::class);

        $html = $pdf->buildBlankBadgesHtml($event, 5);
        self::assertSame(5, substr_count($html, 'class="badge badge--blank"'));

        $bytes = $pdf->generateBlankBadges($event, 5);
        self::assertStringStartsWith('%PDF', $bytes);
    }

    public function testBadgesOmitCrisisFooterWhenPhoneIsNotConfigured(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);
        $this->givePdfRenderedEventARealLogo($eventRepository, $event);
        $this->makeIst($container, $event, 'crisis', 'CrisisNick', UserStatus::Paid);

        $repo = $this->getService($app, ParticipantRepository::class);
        $participants = $repo->getParticipantsForBadges($event, [ParticipantRole::Ist]);
        $mine = array_slice(
            array_values(array_filter($participants, fn (Participant $p) => $p->nickname === 'CrisisNick')),
            0,
            1,
        );
        self::assertCount(1, $mine);

        $pdf = $this->getService($app, PdfGenerator::class);

        $html = $pdf->buildBadgesHtml($event, $mine);
        self::assertStringNotContainsString('class="badge-crisis"', $html);

        $blankHtml = $pdf->buildBlankBadgesHtml($event, 2);
        self::assertStringNotContainsString('class="badge-crisis"', $blankHtml);
    }
}
