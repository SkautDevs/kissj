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

    public function testDoesNotFindParticipantOfAnotherEvent(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $otherEvent = $eventRepository->get(1);
        $authorizedEvent = $this->getTestSlugEvent($eventRepository);

        $userService = $this->getService($app, UserService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $istRepository = $this->getService($app, IstRepository::class);

        $otherUser = $userService->registerEmailUser('tie-other-' . uniqid('', true) . '@example.com', $otherEvent);
        $otherParticipant = $userService->createParticipantSetRole($otherUser, 'ist');
        $authorizedUser = $userService->registerEmailUser(
            'tie-authorized-' . uniqid('', true) . '@example.com',
            $authorizedEvent,
        );
        $authorizedParticipant = $userService->createParticipantSetRole($authorizedUser, 'ist');

        $otherTieCode = $istRepository->get($otherParticipant->id)->tieCode;
        $authorizedTieCode = $istRepository->get($authorizedParticipant->id)->tieCode;

        self::assertNull($participantRepository->findOneByTieCodeAndEvent($otherTieCode, $authorizedEvent));

        $found = $participantRepository->findOneByTieCodeAndEvent($authorizedTieCode, $authorizedEvent);
        self::assertNotNull($found);
        self::assertSame($authorizedParticipant->id, $found->id);
    }
}
