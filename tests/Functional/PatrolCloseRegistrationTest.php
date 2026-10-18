<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class PatrolCloseRegistrationTest extends AppTestCase
{
    public function testPatrolLeaderCloseRegistrationPersists(): void
    {
        $app = $this->getTestApp();

        $event = $this->configurePatrolCounts($app, 1, 5);
        $patrolLeader = $this->createPatrolLeader($app, $event, 'patrol-close@example.com');
        $patrolLeader->patrolName = 'Test Patrol';
        $patrolLeader->firstName = 'Leader';
        $patrolLeader->lastName = 'Test';
        $patrolLeader->gender = 'woman';
        $patrolLeader->birthDate = DateTimeUtils::getDateTime('1995-05-15');
        $patrolLeader->permanentResidence = '123 Test Street, Test City';
        $this->getService($app, PatrolLeaderRepository::class)->persist($patrolLeader);
        $this->addPatrolParticipant($app, $patrolLeader, function (PatrolParticipant $p): void {
            $p->firstName = 'Val';
            $p->lastName = 'Id';
            $p->nickname = 'Vali';
            $p->permanentResidence = '1 Scout Street, Scout Town';
            $p->gender = 'man';
            $p->birthDate = DateTimeUtils::getDateTime('1990-01-01');
            $p->healthProblems = 'None';
            $p->psychicalHealthProblems = 'None';
            $p->notes = '';
            $p->email = 'patrol-close-member@example.com';
        });

        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $patrolLeader = $patrolLeaderRepository->get($patrolLeader->id);

        $this->initializeMailerSettings($app, $event);
        $this->getService($app, UserService::class)->setUserOpen($patrolLeader->getUserButNotNull());

        $participantService = $this->getService($app, ParticipantService::class);
        $participantService->closeRegistration($patrolLeader);

        $reloaded = $patrolLeaderRepository->get($patrolLeader->id);

        self::assertNotNull($reloaded->registrationCloseDate);
        self::assertSame(UserStatus::Closed, $reloaded->getUserButNotNull()->status);
    }

    public function testPatrolLeaderCloseRegistrationRoutePersists(): void
    {
        $app = $this->getTestApp();

        $event = $this->configurePatrolCounts($app, 1, 5);
        $patrolLeader = $this->createPatrolLeader($app, $event, 'patrol-close-route@example.com');
        $patrolLeader->patrolName = 'Route Patrol';
        $patrolLeader->firstName = 'Leader';
        $patrolLeader->lastName = 'Route';
        $patrolLeader->gender = 'woman';
        $patrolLeader->birthDate = DateTimeUtils::getDateTime('1995-05-15');
        $patrolLeader->permanentResidence = '123 Test Street, Test City';
        $this->getService($app, PatrolLeaderRepository::class)->persist($patrolLeader);
        $this->addPatrolParticipant($app, $patrolLeader, function (PatrolParticipant $p): void {
            $p->firstName = 'Val';
            $p->lastName = 'Id';
            $p->nickname = 'Vali';
            $p->permanentResidence = '1 Scout Street, Scout Town';
            $p->gender = 'man';
            $p->birthDate = DateTimeUtils::getDateTime('1990-01-01');
            $p->healthProblems = 'None';
            $p->psychicalHealthProblems = 'None';
            $p->notes = '';
            $p->email = 'patrol-close-route-member@example.com';
        });

        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $patrolLeader = $patrolLeaderRepository->get($patrolLeader->id);

        $this->initializeMailerSettings($app, $event);

        $_SESSION['user'] = ['id' => $patrolLeader->getUserButNotNull()->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/patrol/closeRegistration',
            'POST',
        ));
        self::assertSame(302, $response->getStatusCode());

        $reloaded = $patrolLeaderRepository->get($patrolLeader->id);
        self::assertNotNull($reloaded->registrationCloseDate);
        self::assertSame(UserStatus::Closed, $reloaded->getUserButNotNull()->status);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function configurePatrolCounts(App $app, int $min, int $max): Event
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get(1);
        $event->minimalPatrolParticipantsCount = $min;
        $event->maximalPatrolParticipantsCount = $max;
        $eventRepository->persist($event);

        return $event;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPatrolLeader(App $app, Event $event, string $email): PatrolLeader
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser($email, $event);

        $participant = $userService->createParticipantSetRole($user, 'pl');

        return $this->getService($app, PatrolLeaderRepository::class)->get($participant->id);
    }

    /**
     * @param App<ContainerInterface> $app
     * @param (callable(PatrolParticipant): mixed)|null $fill
     */
    private function addPatrolParticipant(App $app, PatrolLeader $patrolLeader, ?callable $fill = null): void
    {
        $repository = $this->getService($app, PatrolParticipantRepository::class);
        $participant = new PatrolParticipant();
        $participant->patrolLeader = $patrolLeader;
        if ($fill !== null) {
            $fill($participant);
        }
        $repository->persist($participant);
    }

}
