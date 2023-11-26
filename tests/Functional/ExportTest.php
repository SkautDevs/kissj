<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Export\ExportService;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolService;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class ExportTest extends AppTestCase
{
    public function testExportMedicalData(): void
    {
        $app = $this->getTestApp();
        /** @var ContainerInterface $container */
        $container = $app->getContainer();

        /** @var ExportService $exportService */
        $exportService = $container->get(ExportService::class);
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var PatrolService $patrolService */
        $patrolService = $container->get(PatrolService::class);
        /** @var ParticipantService $participantService */
        $participantService = $container->get(ParticipantService::class);

        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $testEvent = $eventRepository->get(1);

        for ($i = 0; $i < 10; $i++) {
            $email = 'test-' . $i . '@example.com';
            $user = $userService->registerEmailUser($email, $testEvent);
            $patrolLeader = $patrolService->getPatrolLeader($user);
            $participantService->addParamsIntoParticipant($patrolLeader, [
                    'patrolName' => 'my great patrol no.' . $i,
                    'firstName' => 'leader no.' . $i,
                    'lastName' => 'leaderový',
                    'nickname' => 'burákové máslo ' . $i,
                    'birthDate' => (DateTimeUtils::getDateTime())->format(DATE_ATOM),
                    'gender' => 'High Elves',
                    'permanentResidence' => 'Kalimdor',
                    'country' => 'Azeroth',
                    'scoutUnit' => 'attack helicopter',
                    'email' => 'test' . $i . '@example.com',
                    'foodPreferences' => 'trolls',
                    'healthProblems' => 'some',
                    'notes' => 'some note',
                ]
            );
        }

        $admin = $this->createAdminUser($app);
        $this->markTestSkipped('TODO fix role for PL');
        $rows = $exportService->healthDataToCSV($testEvent, $admin);

        $this->assertCount(11, $rows);

        $this->assertNull($rows[0][0]);
        $this->assertNull($rows[0][1]);
        $this->assertSame('leader0', $rows[1][0]);
        $this->assertSame('leaderový', $rows[1][1]);
        $this->assertSame('leader1', $rows[2][0]);
        $this->assertSame('leaderový', $rows[2][1]);
    }
}
