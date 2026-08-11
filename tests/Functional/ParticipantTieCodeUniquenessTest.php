<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserService;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class ParticipantTieCodeUniquenessTest extends AppTestCase
{
    public function testCreatedParticipantHasSixUppercaseLetterTieCode(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);

        $participant = $this->getService($app, ParticipantRepository::class)
            ->getParticipantFromUser($this->createUserWithParticipant($app, $event));

        self::assertMatchesRegularExpression('/^[A-Z]{6}$/', $participant->tieCode);
    }

    public function testEnsureUniqueTieCodeRegeneratesOnCollision(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $existing = $participantRepository->getParticipantFromUser($this->createUserWithParticipant($app, $event));
        $existingCode = $existing->tieCode;

        $colliding = $participantRepository->getParticipantFromUser($this->createUserWithParticipant($app, $event));
        $colliding->tieCode = $existingCode;

        $participantRepository->ensureUniqueTieCode($colliding);

        self::assertNotSame($existingCode, $colliding->tieCode);
        self::assertMatchesRegularExpression('/^[A-Z]{6}$/', $colliding->tieCode);
        self::assertFalse($participantRepository->isTieCodeInUse($colliding->tieCode));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createUserWithParticipant(App $app, Event $event): User
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('tie-code-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ist');

        return $user;
    }
}
