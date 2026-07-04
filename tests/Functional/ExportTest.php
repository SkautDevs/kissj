<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Export\ExportService;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Tests\AppTestCase;

class ExportTest extends AppTestCase
{
    public function testExportMedicalData(): void
    {
        $app = $this->getTestApp();

        $exportService = $this->getService($app, ExportService::class);
        $userService = $this->getService($app, UserService::class);
        $participantService = $this->getService($app, ParticipantService::class);
        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $userRepository = $this->getService($app, UserRepository::class);

        $eventRepository = $this->getService($app, EventRepository::class);
        $testEvent = $eventRepository->get(1);

        // Increase capacity to allow new patrol leaders
        $testEvent->maximalClosedPatrolsCount = 100;
        $eventRepository->persist($testEvent);

        for ($i = 0; $i < 10; $i++) {
            $email = 'export-test-' . $i . '@example.com';
            $user = $userService->registerEmailUser($email, $testEvent);
            // First set the role to create the PatrolLeader participant
            $participant = $userService->createParticipantSetRole($user, 'pl');
            // Then get it as PatrolLeader from repository
            $patrolLeader = $patrolLeaderRepository->get($participant->id);
            $participantService->addParamsIntoParticipant(
                $patrolLeader,
                [
                    'patrolName' => 'my great patrol no.' . $i,
                    'firstName' => 'leader' . $i,
                    'lastName' => 'leaderový',
                    'nickname' => 'burákové máslo ' . $i,
                    'birthDate' => (DateTimeUtils::getDateTime())->format(DATE_ATOM),
                    'gender' => 'male',
                    'permanentResidence' => 'Kalimdor',
                    'country' => 'Azeroth',
                    'scoutUnit' => 'attack helicopter',
                    'email' => 'export-test' . $i . '@example.com',
                    'foodPreferences' => 'trolls',
                    'healthProblems' => 'some',
                    'notes' => 'some note',
                ]
            );

            // Set user as Paid (export only includes Paid participants)
            $user->status = UserStatus::Paid;
            $userRepository->persist($user);
        }

        $admin = $this->createAdminUser($app);
        $rows = $exportService->healthDataToCSV($testEvent, $admin);

        // First row is header, rest are data rows; the fresh DB holds exactly the 10 created leaders
        self::assertCount(11, $rows);

        // Check header row exists (columns: id, role, status, contingent, name, surname, ...)
        self::assertIsArray($rows[0]);
        self::assertSame('name', $rows[0][4]);
        self::assertSame('surname', $rows[0][5]);

        // Find our created test data by looking for 'leaderový' surname (column index 5)
        $foundLeader0 = false;
        $foundLeader1 = false;
        foreach ($rows as $row) {
            if (isset($row[4]) && $row[4] === 'leader0' && isset($row[5]) && $row[5] === 'leaderový') {
                $foundLeader0 = true;
            }
            if (isset($row[4]) && $row[4] === 'leader1' && isset($row[5]) && $row[5] === 'leaderový') {
                $foundLeader1 = true;
            }
        }
        self::assertTrue($foundLeader0, 'Should find leader0 in export');
        self::assertTrue($foundLeader1, 'Should find leader1 in export');
    }

    // ExportService has resolved a patrol participant's contingent through its leader since long
    // before this method existed; the assertions below pin that behaviour while the resolution
    // itself moves onto the entity, where the admin food stats screen can reuse it.
    public function testContingentFallsBackToPatrolLeaderForPatrolParticipant(): void
    {
        $app = $this->getTestApp();

        $exportService = $this->getService($app, ExportService::class);
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->findBySlug('obrok37');
        self::assertNotNull($event);

        $suffix = bin2hex(random_bytes(4));

        $leaderUser = $userService->registerEmailUser('export-contingent-pl-' . $suffix . '@example.com', $event);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'pl');

        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $patrolLeaderRepository->get($leaderParticipant->id);
        // an untranslated value: the Symfony translator returns unknown keys unchanged, which
        // keeps the assertion independent of the yaml files
        $patrolLeader->contingent = 'alfa' . $suffix;
        $patrolLeaderRepository->persist($patrolLeader);

        $ppUser = $userService->registerEmailUser('export-contingent-pp-' . $suffix . '@example.com', $event);
        $patrolParticipant = new PatrolParticipant();
        $patrolParticipant->user = $ppUser;
        $patrolParticipant->patrolLeader = $patrolLeader;
        $participantRepository->persist($patrolParticipant);
        $ppUser->status = UserStatus::Paid;
        $userRepository->persist($ppUser);

        $reloaded = $participantRepository->getParticipantById($patrolParticipant->id, $event);
        self::assertInstanceOf(PatrolParticipant::class, $reloaded);

        self::assertNull($reloaded->contingent);
        self::assertSame('alfa' . $suffix, $reloaded->getOwnOrLeaderContingent());
        self::assertSame('alfa' . $suffix, $exportService->getContingentTranslated($reloaded));

        // an own contingent always wins over the leader's
        $reloaded->contingent = 'zulu' . $suffix;
        $participantRepository->persist($reloaded);

        self::assertSame('zulu' . $suffix, $reloaded->getOwnOrLeaderContingent());
        self::assertSame('zulu' . $suffix, $exportService->getContingentTranslated($reloaded));
    }
}
