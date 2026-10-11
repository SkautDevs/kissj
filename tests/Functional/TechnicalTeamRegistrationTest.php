<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\TechnicalTeam\TechnicalTeamLeader;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Tests\AppTestCase;

class TechnicalTeamRegistrationTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    /** @var App<ContainerInterface>|null */
    private ?App $app = null;

    public function testLoginWithValidTtlTokenGrantsAccess(): void
    {
        $this->enableTechnicalTeams('abc123');

        $response = $this->getTestApp(false)->handle(
            $this->createRequest(self::BASE_URL . '/login')->withQueryParams(['ttl_token' => 'abc123']),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertSame($this->getTestSlugEvent($this->getEventRepository())->id, $_SESSION['ttl_access_granted']);
    }

    public function testLoginWithWrongTtlTokenDoesNotGrantAccess(): void
    {
        $this->enableTechnicalTeams('abc123');

        $response = $this->getTestApp(false)->handle(
            $this->createRequest(self::BASE_URL . '/login')->withQueryParams(['ttl_token' => 'wrong']),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertFalse(isset($_SESSION['ttl_access_granted']));
    }

    public function testChooseRoleHighlightsUnlockedTtlButton(): void
    {
        $this->enableTechnicalTeams('abc123');
        $user = $this->createUserWithoutRole('ttl-reg-3@example.com');
        $_SESSION['user'] = ['id' => $user->id];
        $_SESSION['ttl_access_granted'] = $user->event->id;

        $body = $this->fetchChooseRoleBody();

        self::assertStringContainsString('value="ttl"', $body);
        self::assertStringContainsString('btn-highlight', $body);
        self::assertStringContainsString('Registrovat technický tým', $body);
    }

    public function testChooseRoleHidesTtlWithoutToken(): void
    {
        $this->enableTechnicalTeams('abc123');
        $user = $this->createUserWithoutRole('ttl-reg-4@example.com');
        $_SESSION['user'] = ['id' => $user->id];

        $body = $this->fetchChooseRoleBody();

        self::assertStringNotContainsString('value="ttl"', $body);
        self::assertStringNotContainsString('btn-highlight', $body);
    }

    public function testSetRoleTtlWithoutTokenIsRejected(): void
    {
        $this->enableTechnicalTeams('abc123');
        $user = $this->createUserWithoutRole('ttl-reg-5@example.com');
        $_SESSION['user'] = ['id' => $user->id];

        $response = $this->postRole('ttl');

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('chooseRole', $response->getHeaderLine('Location'));
        $this->assertNoParticipantCreated($user);
    }

    public function testSetRoleTtlWithTokenCreatesTechnicalTeamLeader(): void
    {
        $this->enableTechnicalTeams('abc123');
        $user = $this->createUserWithoutRole('ttl-reg-6@example.com');
        $_SESSION['user'] = ['id' => $user->id];
        $_SESSION['ttl_access_granted'] = $user->event->id;

        $response = $this->postRole('ttl');

        self::assertSame(302, $response->getStatusCode());
        $participant = $this->getFreshParticipantRepository()->getParticipantFromUser($user);
        self::assertInstanceOf(TechnicalTeamLeader::class, $participant);
    }

    public function testSetRoleTtlRejectedWhenTokenGrantedForAnotherEvent(): void
    {
        $this->enableTechnicalTeams('abc123');
        $user = $this->createUserWithoutRole('ttl-reg-other-event@example.com');
        $_SESSION['user'] = ['id' => $user->id];
        $_SESSION['ttl_access_granted'] = $user->event->id + 1000;

        $response = $this->postRole('ttl');

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('chooseRole', $response->getHeaderLine('Location'));
        $this->assertNoParticipantCreated($user);
    }

    public function testLoginWithArrayTtlTokenIsTreatedAsAbsent(): void
    {
        $this->enableTechnicalTeams('abc123');

        $response = $this->getTestApp(false)->handle(
            $this->createRequest(self::BASE_URL . '/login')->withQueryParams(['ttl_token' => ['x']]),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString('name="ttl_token"', (string)$response->getBody());
    }

    public function testSetRoleTtpIsAlwaysRejected(): void
    {
        $this->enableTechnicalTeams('abc123');
        $user = $this->createUserWithoutRole('ttl-reg-7@example.com');
        $_SESSION['user'] = ['id' => $user->id];
        $_SESSION['ttl_access_granted'] = $user->event->id;

        $response = $this->postRole('ttp');

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('chooseRole', $response->getHeaderLine('Location'));
        $this->assertNoParticipantCreated($user);
    }

    public function testSetRoleTtlRejectedWhenRoleDisabled(): void
    {
        $this->enableTechnicalTeams('abc123');
        $user = $this->createUserWithoutRole('ttl-reg-8@example.com');
        $event = $this->getTestSlugEvent($this->getEventRepository());
        $this->mutateEventForTest($this->getContainer(), $event, ['allowTechnicalTeams' => false]);
        $_SESSION['user'] = ['id' => $user->id];
        $_SESSION['ttl_access_granted'] = $user->event->id;

        $response = $this->postRole('ttl');

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('chooseRole', $response->getHeaderLine('Location'));
        $this->assertNoParticipantCreated($user);
    }

    private function enableTechnicalTeams(string $token): void
    {
        $event = $this->getTestSlugEvent($this->getEventRepository());
        $this->mutateEventForTest($this->getContainer(), $event, [
            'allowTechnicalTeams' => true,
            'technicalTeamRegistrationToken' => $token,
        ]);
    }

    private function createUserWithoutRole(string $email): User
    {
        $event = $this->getTestSlugEvent($this->getEventRepository());
        /** @var UserService $userService */
        $userService = $this->getContainer()->get(UserService::class);

        return $userService->registerEmailUser($email, $event);
    }

    private function fetchChooseRoleBody(): string
    {
        $response = $this->getTestApp(false)->handle($this->createRequest(self::BASE_URL . '/chooseRole'));
        self::assertSame(200, $response->getStatusCode());

        return (string)$response->getBody();
    }

    private function postRole(string $role): ResponseInterface
    {
        return $this->getTestApp(false)->handle(
            $this->createRequest(self::BASE_URL . '/setRole', 'POST', ['role' => $role]),
        );
    }

    private function assertNoParticipantCreated(User $user): void
    {
        self::assertNull($this->getFreshParticipantRepository()->findParticipantFromUser($user));
        /** @var UserRepository $userRepository */
        $userRepository = $this->getTestApp(false)->getContainer()->get(UserRepository::class);
        self::assertSame(UserStatus::WithoutRole, $userRepository->get($user->id)->status);
    }

    /**
     * @return App<ContainerInterface>
     */
    private function getApp(): App
    {
        $this->app ??= $this->getTestApp();

        return $this->app;
    }

    private function getContainer(): ContainerInterface
    {
        return $this->getApp()->getContainer();
    }

    private function getEventRepository(): EventRepository
    {
        /** @var EventRepository $repository */
        $repository = $this->getContainer()->get(EventRepository::class);

        return $repository;
    }

    private function getFreshParticipantRepository(): ParticipantRepository
    {
        /** @var ParticipantRepository $repository */
        $repository = $this->getTestApp(false)->getContainer()->get(ParticipantRepository::class);

        return $repository;
    }
}
