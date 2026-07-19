<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantRepository;
use kissj\User\UserService;
use Tests\AppTestCase;

class ParticipantTieCodeLookupTest extends AppTestCase
{
    public function testFindOneByTieCodeAndEventMatchesLowercaseInput(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);

        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('tie-lookup-' . uniqid('', true) . '@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $storedCode = $this->getService($app, IstRepository::class)->get($participant->id)->tieCode;

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $found = $participantRepository->findOneByTieCodeAndEvent(strtolower($storedCode), $event);

        self::assertNotNull($found);
        self::assertSame($participant->id, $found->id);
    }
}
