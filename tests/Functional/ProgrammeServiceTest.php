<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeEnrolmentRepository;
use kissj\Programme\ProgrammeEnrolmentService;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeService;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Tests\AppTestCase;

class ProgrammeServiceTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testDeleteProgrammeCascades(): void
    {
        $app = $this->getTestApp();
        $this->openProgrammeEnrolmentWindow($app);
        $enrolmentService = $this->getService($app, ProgrammeEnrolmentService::class);
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $enrolmentRepository = $this->getService($app, ProgrammeEnrolmentRepository::class);
        $event = $this->getService($app, EventRepository::class)->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme = new Programme();
        $programme->name = 'doomed';
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime('2027-06-05 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-05 12:00');
        $programme->capacity = 5;
        $programme->isAutoenrolled = false;
        $programme->event = $event;
        $programme->section = $this->createProgrammeSection($app->getContainer(), $event, 'Program');
        $programme->roles = [ParticipantRole::Ist];
        $programmeRepository->persist($programme);
        $user = $this->getService($app, UserService::class)->registerEmailUser('doomed-ist@example.com', $event);
        $ist = $this->getService($app, UserService::class)->createParticipantSetRole($user, 'ist');
        $userRepository = $this->getService($app, UserRepository::class);
        $paidUser = $userRepository->get($user->id);
        $paidUser->status = UserStatus::Paid;
        $userRepository->persist($paidUser);
        self::assertNull($enrolmentService->enrol($ist, $programme->id)->error);

        $this->getService($app, ProgrammeService::class)->deleteProgramme($programme);

        $reloaded = $programmeRepository->findOneBy(['id' => $programme->id]);
        self::assertInstanceOf(Programme::class, $reloaded);
        self::assertTrue($reloaded->isDeleted());
        self::assertSame(0, $enrolmentRepository->countActiveForProgramme($programme));
    }
}
