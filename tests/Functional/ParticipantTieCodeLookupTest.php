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

    public function testFindsRecipientWhenAnotherEventHasTheSameTieCode(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $shadowingEvent = $eventRepository->get(1);
        $recipientEvent = $this->getTestSlugEvent($eventRepository);

        $userService = $this->getService($app, UserService::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        // the shadowing participant is created first, so an unscoped lookup finds it instead of the recipient
        $shadowingUser = $userService->registerEmailUser('tie-shadow-' . uniqid('', true) . '@example.com', $shadowingEvent);
        $shadowingParticipant = $userService->createParticipantSetRole($shadowingUser, 'ist');
        $recipientUser = $userService->registerEmailUser('tie-recipient-' . uniqid('', true) . '@example.com', $recipientEvent);
        $recipientParticipant = $userService->createParticipantSetRole($recipientUser, 'ist');

        // no unique index guards tie_code, so cross-event duplicates can exist in production data
        $sharedTieCode = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);
        $shadowingParticipant->tieCode = $sharedTieCode;
        $participantRepository->persist($shadowingParticipant);
        $recipientParticipant->tieCode = $sharedTieCode;
        $participantRepository->persist($recipientParticipant);

        $found = $participantRepository->findOneByTieCodeAndEvent($sharedTieCode, $recipientEvent);

        self::assertNotNull($found);
        self::assertSame($recipientParticipant->id, $found->id);
    }
}
