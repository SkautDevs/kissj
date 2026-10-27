<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Participant;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserRole;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Tests\AppTestCase;

class ProgrammeJsonTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';

    public function testJsonAssignToPreregisteredProgrammeReturnsConflictInsteadOf500(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'json ceremony', null, 999, isPreregistered: true);
        $ist = $this->createPaidIstWithEmail($app, 'json-preregistered-assignee@example.com');
        $app = $this->createLoggedAdmin($app, 'json-preregistered-admin@example.com');

        $response = $app->handle($this->createRequest(
            '/v3/event/test-event-slug/admin/programmes/' . $programme->id . '/assign/' . $ist->id,
            'POST',
        ));
        self::assertSame(409, $response->getStatusCode());
        $payload = json_decode((string)$response->getBody(), true);
        self::assertIsArray($payload);
        self::assertSame('flash.warning.programmePreregisteredNoAssign', $payload['translationKey']);

        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testJsonAssignAndRemoveReturnEmptyObjectOnSuccess(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'json assign-remove workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'json-assign-remove-ist@example.com');
        $app = $this->createLoggedAdmin($app, 'json-assign-remove-admin@example.com');
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);

        $assignResponse = $app->handle($this->createRequest(
            '/v3/event/test-event-slug/admin/programmes/' . $programme->id . '/assign/' . $ist->id,
            'POST',
        ));
        self::assertSame(200, $assignResponse->getStatusCode());
        self::assertSame([], json_decode((string)$assignResponse->getBody(), true));
        self::assertSame(1, $registrationRepository->countActiveForProgramme($programme));

        $removeResponse = $app->handle($this->createRequest(
            '/v3/event/test-event-slug/admin/programmes/' . $programme->id . '/remove/' . $ist->id,
            'POST',
        ));
        self::assertSame(200, $removeResponse->getStatusCode());
        self::assertSame([], json_decode((string)$removeResponse->getBody(), true));
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testJsonAssignCancelledParticipantReturnsConflictInsteadOf500(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'json cancelled workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'json-cancelled-assignee@example.com');
        $userRepository = $this->getService($app, UserRepository::class);
        $user = $ist->getUserButNotNull();
        $user->status = UserStatus::Cancelled;
        $userRepository->persist($user);
        $app = $this->createLoggedAdmin($app, 'json-cancelled-admin@example.com');

        $response = $app->handle($this->createRequest(
            '/v3/event/test-event-slug/admin/programmes/' . $programme->id . '/assign/' . $ist->id,
            'POST',
        ));
        self::assertSame(409, $response->getStatusCode());
        $payload = json_decode((string)$response->getBody(), true);
        self::assertIsArray($payload);
        self::assertSame('flash.warning.programmeCancelledNoAssign', $payload['translationKey']);

        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        self::assertSame(0, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testJsonAssignUnknownParticipantReturnsConflictInsteadOf500(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'json unknown participant workshop', null, 5);
        $app = $this->createLoggedAdmin($app, 'json-unknown-participant-admin@example.com');

        $response = $app->handle($this->createRequest(
            '/v3/event/test-event-slug/admin/programmes/' . $programme->id . '/assign/999999999',
            'POST',
        ));

        $this->assertConflictWithKey($response, 'flash.warning.programmeParticipantNotFound');
    }

    public function testJsonRemoveUnknownParticipantReturnsConflictInsteadOf500(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'json unknown participant remove workshop', null, 5);
        $app = $this->createLoggedAdmin($app, 'json-unknown-participant-remove-admin@example.com');

        $response = $app->handle($this->createRequest(
            '/v3/event/test-event-slug/admin/programmes/' . $programme->id . '/remove/999999999',
            'POST',
        ));

        $this->assertConflictWithKey($response, 'flash.warning.programmeParticipantNotFound');
    }

    public function testJsonDoubleAssignReturnsAlreadyAssignedConflict(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'json double assign workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'json-double-assign-ist@example.com');
        $app = $this->createLoggedAdmin($app, 'json-double-assign-admin@example.com');
        $path = '/v3/event/test-event-slug/admin/programmes/' . $programme->id . '/assign/' . $ist->id;

        self::assertSame(200, $app->handle($this->createRequest($path, 'POST'))->getStatusCode());
        $response = $app->handle($this->createRequest($path, 'POST'));

        $this->assertConflictWithKey($response, 'flash.warning.programmeAlreadyAssigned');
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);
        self::assertSame(1, $registrationRepository->countActiveForProgramme($programme));
    }

    public function testJsonRemoveNotAssignedParticipantReturnsNotAssignedConflict(): void
    {
        $app = $this->getTestApp();
        $programme = $this->createProgramme($app, 'json remove not assigned workshop', null, 5);
        $ist = $this->createPaidIstWithEmail($app, 'json-remove-not-assigned-ist@example.com');
        $app = $this->createLoggedAdmin($app, 'json-remove-not-assigned-admin@example.com');

        $response = $app->handle($this->createRequest(
            '/v3/event/test-event-slug/admin/programmes/' . $programme->id . '/remove/' . $ist->id,
            'POST',
        ));

        $this->assertConflictWithKey($response, 'flash.warning.programmeNotAssigned');
    }

    private function assertConflictWithKey(ResponseInterface $response, string $translationKey): void
    {
        self::assertSame(409, $response->getStatusCode());
        $payload = json_decode((string)$response->getBody(), true);
        self::assertIsArray($payload);
        self::assertSame($translationKey, $payload['translationKey']);
    }

    /**
     * @param App<ContainerInterface> $app
     * @return App<ContainerInterface>
     */
    private function createLoggedAdmin(App $app, string $email): App
    {
        $user = $this->registerUser($app, $email);
        $userRepository = $this->getService($app, UserRepository::class);
        $user->role = UserRole::Admin;
        $userRepository->persist($user);

        return $this->loginAs($app, $user);
    }

    /**
     * @param App<ContainerInterface> $app
     * @return App<ContainerInterface>
     */
    private function loginAs(App $app, User $user): App
    {
        $loginTokenRepository = $this->getService($app, LoginTokenRepository::class);

        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $loginTokenRepository->persist($loginToken);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . self::TEST_EVENT_SLUG . '/tryLogin/' . $loginToken->token,
        ));
        self::assertSame(302, $response->getStatusCode());

        // the container (and the UserRegeneration singleton reading $_SESSION at construction
        // time) is scoped to one $app instance, so a fresh one is needed to pick up the login
        return $this->getTestApp(false);
    }

    /**
     * @param App<ContainerInterface> $app
     * @param list<ParticipantRole>|null $roles
     */
    private function createProgramme(
        App $app,
        string $name,
        ?string $groupName,
        int $capacity,
        string $start = '2027-06-05 10:00',
        string $end = '2027-06-05 12:00',
        ?array $roles = null,
        bool $isPreregistered = false,
    ): Programme {
        $programmeRepository = $this->getService($app, ProgrammeRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $programme = new Programme();
        $programme->name = $name;
        $programme->description = 'description';
        $programme->place = 'somewhere';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($end);
        $programme->capacity = $capacity;
        $programme->isPreregistered = $isPreregistered;
        $programme->exclusiveGroupName = $groupName;
        $programme->event = $eventRepository->getOneBy(['slug' => self::TEST_EVENT_SLUG]);
        $programme->setTargetRoles(
            $roles ?? [ParticipantRole::Ist, ParticipantRole::TroopLeader, ParticipantRole::TroopParticipant],
        );
        $programmeRepository->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPaidIstWithEmail(App $app, string $email): Participant
    {
        $user = $this->registerUser($app, $email);
        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $userRepository = $this->getService($app, UserRepository::class);
        $user = $userRepository->get($user->id);
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        return $participant;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function registerUser(App $app, string $email): User
    {
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            $event = $eventRepository->get(1);
        }

        return $userService->registerEmailUser($email, $event);
    }
}
