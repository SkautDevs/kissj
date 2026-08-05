<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\User\UserService;
use Tests\AppTestCase;

// the base arbiter disallows 'contingent' and 'scarf' for every role of the default test
// event, which makes them the probe fields for the mass-assignment filter
class ParticipantMassAssignmentTest extends AppTestCase
{
    private const string BASE_URL = '/v2/event/test-event-slug';

    public function testChangeDetailsIgnoresArbiterDisallowedFields(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug') ?? $eventRepository->get(1);

        $user = $userService->registerEmailUser('mass-assign-ist@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/changeDetails',
            'POST',
            [
                'firstName' => 'Legit',
                'lastName' => 'User',
                'contingent' => 'ContingentInjection',
                'scarf' => Participant::SCARF_YES,
            ],
        ));

        self::assertSame(302, $response->getStatusCode());

        $saved = $participantRepository->get($participant->id);
        self::assertInstanceOf(Participant::class, $saved);
        self::assertSame('Legit', $saved->firstName);
        self::assertSame('User', $saved->lastName);
        self::assertNull($saved->contingent, 'arbiter-disallowed field must not be written from POST');
        self::assertNull($saved->scarf, 'arbiter-disallowed field must not be written from POST');
    }

    public function testChangeDetailsPreservesExistingHiddenFieldValues(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug') ?? $eventRepository->get(1);

        $user = $userService->registerEmailUser('mass-preserve-ist@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participant->scarf = Participant::SCARF_YES;
        $participantRepository->persist($participant);

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/changeDetails',
            'POST',
            ['firstName' => 'Keeper', 'lastName' => 'OfScarf'],
        ));

        self::assertSame(302, $response->getStatusCode());

        $saved = $participantRepository->get($participant->id);
        self::assertInstanceOf(Participant::class, $saved);
        self::assertSame('Keeper', $saved->firstName);
        self::assertSame(
            Participant::SCARF_YES,
            $saved->scarf,
            'hidden-field value must survive a details save instead of being wiped',
        );
    }

    public function testChangeDetailsStillNullsOmittedAllowedFields(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug') ?? $eventRepository->get(1);

        $user = $userService->registerEmailUser('mass-null-ist@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participant->nickname = 'OldNick';
        $participantRepository->persist($participant);

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/changeDetails',
            'POST',
            ['firstName' => 'Nick', 'lastName' => 'Less'],
        ));

        self::assertSame(302, $response->getStatusCode());

        $saved = $participantRepository->get($participant->id);
        self::assertInstanceOf(Participant::class, $saved);
        self::assertNull($saved->nickname, 'allowed field omitted from POST keeps the null-on-absence semantics');
    }
}
