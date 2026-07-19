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
use kissj\Participant\Patrol\PatrolService;
use kissj\Participant\RegistrationCloseResult;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class PatrolLeaderCloseValidationTest extends AppTestCase
{
    public function testWarnsWhenTooFewPatrolParticipants(): void
    {
        $app = $this->getTestApp();
        $event = $this->configurePatrolCounts($app, 2, 5);
        $patrolLeader = $this->createPatrolLeader($app, $event, 'patrol-too-few@example.com');

        $result = $this->closeResultFor($app, $patrolLeader);

        self::assertContains('flash.warning.plTooFewParticipants', array_column($result->warnings, 'key'));
    }

    public function testWarnsWhenPatrolParticipantHasInvalidData(): void
    {
        $app = $this->getTestApp();
        $event = $this->configurePatrolCounts($app, 0, 5);
        $patrolLeader = $this->createPatrolLeader($app, $event, 'patrol-invalid@example.com');
        // participant with no filled fields — required data missing
        $this->addPatrolParticipant($app, $patrolLeader);

        $result = $this->closeResultFor($app, $patrolLeader);

        self::assertContains('flash.warning.plWrongDataParticipant', array_column($result->warnings, 'key'));
    }

    public function testValidPatrolProducesNoPatrolWarnings(): void
    {
        $app = $this->getTestApp();
        $event = $this->configurePatrolCounts($app, 1, 5);
        $patrolLeader = $this->createPatrolLeader($app, $event, 'patrol-valid@example.com');
        $this->addPatrolParticipant($app, $patrolLeader, function (PatrolParticipant $p): void {
            $p->firstName = 'Val';
            $p->lastName = 'Id';
            $p->nickname = 'Vali';
            $p->permanentResidence = '1 Scout Street, Scout Town';
            $p->gender = 'male';
            $p->birthDate = DateTimeUtils::getDateTime('1990-01-01');
            $p->healthProblems = 'None';
            $p->psychicalHealthProblems = 'None';
            $p->notes = '';
            $p->email = 'patrol-valid-member@example.com';
        });

        $result = $this->closeResultFor($app, $patrolLeader);

        $keys = array_column($result->warnings, 'key');
        self::assertNotContains('flash.warning.plTooFewParticipants', $keys);
        self::assertNotContains('flash.warning.plTooManyParticipants', $keys);
        self::assertNotContains('flash.warning.plWrongDataParticipant', $keys);
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
    private function closeResultFor(App $app, PatrolLeader $patrolLeader): RegistrationCloseResult
    {
        // reload so the entity row is fully hydrated and its patrolParticipants relation is fresh
        $fresh = $this->getService($app, PatrolLeaderRepository::class)->get($patrolLeader->id);

        return $this->getService($app, ParticipantService::class)->isCloseRegistrationValid($fresh);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPatrolLeader(App $app, Event $event, string $email): PatrolLeader
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser($email, $event);

        return $this->getService($app, PatrolService::class)->getPatrolLeader($user);
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
