<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopLeaderRepository;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use Slim\App;
use Tests\AppTestCase;

class TroopStatusGuardTest extends AppTestCase
{
    public function testClosedTroopLeaderCannotUntieParticipant(): void
    {
        $this->expectLog(Level::Warning, 'User is trying to change data without open status');
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $leaderUser = $this->createParticipantUser($app, $event, 'tl', 'closed');
        $troopParticipant = $this->createTroopParticipant($app, $event, 'open');
        $this->tie($app, $troopParticipant, $this->getTroopLeader($app, $leaderUser));
        $_SESSION['user'] = ['id' => $leaderUser->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/troop/participant/' . $troopParticipant->id . '/untie',
            'POST',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'dashboard', $event), $response->getHeaderLine('Location'));
        self::assertContains($this->translate($app, 'flash.error.openStatusRequired'), $this->flashed($app));
        self::assertNotNull($this->reloadTroopParticipant($app, $troopParticipant)->troopLeader);
    }

    public function testClosedTroopLeaderCannotOpenUntieConfirmation(): void
    {
        $this->expectLog(Level::Warning, 'User is trying to change data without open status');
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $leaderUser = $this->createParticipantUser($app, $event, 'tl', 'closed');
        $troopParticipant = $this->createTroopParticipant($app, $event, 'open');
        $this->tie($app, $troopParticipant, $this->getTroopLeader($app, $leaderUser));
        $_SESSION['user'] = ['id' => $leaderUser->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/troop/participant/' . $troopParticipant->id . '/showUntie',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'dashboard', $event), $response->getHeaderLine('Location'));
        self::assertContains($this->translate($app, 'flash.error.openStatusRequired'), $this->flashed($app));
    }

    public function testTroopParticipantCannotUntieThemselves(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $troopLeader = $this->getTroopLeader($app, $this->createParticipantUser($app, $event, 'tl', 'open'));
        $troopParticipant = $this->createTroopParticipant($app, $event, 'open');
        $this->tie($app, $troopParticipant, $troopLeader);
        $_SESSION['user'] = ['id' => $troopParticipant->getUserButNotNull()->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/troop/participant/' . $troopParticipant->id . '/untie',
            'POST',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'dashboard', $event), $response->getHeaderLine('Location'));
        self::assertContains($this->translate($app, 'flash.error.notLeader'), $this->flashed($app));
        self::assertSame($troopLeader->id, $this->reloadTroopParticipant($app, $troopParticipant)->troopLeader?->id);
    }

    public function testClosedTroopLeaderOfAnotherTroopGetsWrongTroopRejection(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $ownerLeader = $this->getTroopLeader($app, $this->createParticipantUser($app, $event, 'tl', 'open'));
        $foreignLeaderUser = $this->createParticipantUser($app, $event, 'tl', 'closed');
        $troopParticipant = $this->createTroopParticipant($app, $event, 'open');
        $this->tie($app, $troopParticipant, $ownerLeader);
        $_SESSION['user'] = ['id' => $foreignLeaderUser->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/troop/participant/' . $troopParticipant->id . '/untie',
            'POST',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'dashboard', $event), $response->getHeaderLine('Location'));
        self::assertContains($this->translate($app, 'flash.error.wrongTroop'), $this->flashed($app));
        self::assertSame($ownerLeader->id, $this->reloadTroopParticipant($app, $troopParticipant)->troopLeader?->id);
    }

    public function testOpenTroopLeaderCanUntieParticipant(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $leaderUser = $this->createParticipantUser($app, $event, 'tl', 'open');
        $troopParticipant = $this->createTroopParticipant($app, $event, 'open');
        $this->tie($app, $troopParticipant, $this->getTroopLeader($app, $leaderUser));
        $_SESSION['user'] = ['id' => $leaderUser->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/troop/participant/' . $troopParticipant->id . '/untie',
            'POST',
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertNull($this->reloadTroopParticipant($app, $troopParticipant)->troopLeader);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function processedStatusProvider(): iterable
    {
        yield 'approved' => ['approved'];
        yield 'paid' => ['paid'];
        yield 'cancelled' => ['cancelled'];
    }

    #[DataProvider('processedStatusProvider')]
    public function testProcessedTroopParticipantCannotTieByTieCode(string $status): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $troopLeader = $this->getTroopLeader($app, $this->createParticipantUser($app, $event, 'tl', 'open'));
        $troopParticipant = $this->createTroopParticipant($app, $event, $status);
        $_SESSION['user'] = ['id' => $troopParticipant->getUserButNotNull()->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/troop/tieParticipantToTroopByParticipant',
            'POST',
            ['tieCode' => $troopLeader->tieCode],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'getDashboard', $event), $response->getHeaderLine('Location'));
        self::assertSame([$this->translate($app, 'flash.warning.tpCannotTieInStatus')], $this->flashed($app));
        self::assertNull($this->reloadTroopParticipant($app, $troopParticipant)->troopLeader);
    }

    public function testAlreadyTiedPaidTroopParticipantGetsOnlyStatusWarning(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $troopLeader = $this->getTroopLeader($app, $this->createParticipantUser($app, $event, 'tl', 'open'));
        $troopParticipant = $this->createTroopParticipant($app, $event, 'paid');
        $this->tie($app, $troopParticipant, $troopLeader);
        $_SESSION['user'] = ['id' => $troopParticipant->getUserButNotNull()->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/troop/tieParticipantToTroopByParticipant',
            'POST',
            ['tieCode' => $troopLeader->tieCode],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame([$this->translate($app, 'flash.warning.tpCannotTieInStatus')], $this->flashed($app));
    }

    public function testClosedTroopParticipantCanTieByTieCode(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $troopLeader = $this->getTroopLeader($app, $this->createParticipantUser($app, $event, 'tl', 'open'));
        $troopParticipant = $this->createTroopParticipant($app, $event, 'closed');
        $_SESSION['user'] = ['id' => $troopParticipant->getUserButNotNull()->id];

        $response = $this->getTestApp(false)->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/troop/tieParticipantToTroopByParticipant',
            'POST',
            ['tieCode' => $troopLeader->tieCode],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertSame($this->urlFor($app, 'getDashboard', $event), $response->getHeaderLine('Location'));
        self::assertSame(
            [$this->translate($app, 'flash.success.troopParticipantTiedToTroopLeader')],
            $this->flashed($app),
        );
        self::assertSame($troopLeader->id, $this->reloadTroopParticipant($app, $troopParticipant)->troopLeader?->id);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createParticipantUser(App $app, Event $event, string $role, string $status): User
    {
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('troop-status-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, $role);

        $user->status = UserStatus::from($status);
        $this->getService($app, UserRepository::class)->persist($user);

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createTroopParticipant(App $app, Event $event, string $status): TroopParticipant
    {
        return $this->getService($app, TroopParticipantRepository::class)
            ->getFromUser($this->createParticipantUser($app, $event, 'tp', $status));
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function getTroopLeader(App $app, User $user): TroopLeader
    {
        return $this->getService($app, TroopLeaderRepository::class)->getFromUser($user);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function tie(App $app, TroopParticipant $troopParticipant, TroopLeader $troopLeader): void
    {
        $troopParticipant->troopLeader = $troopLeader;
        $this->getService($app, TroopParticipantRepository::class)->persist($troopParticipant);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function reloadTroopParticipant(App $app, TroopParticipant $troopParticipant): TroopParticipant
    {
        return $this->getService($app, TroopParticipantRepository::class)->get($troopParticipant->id);
    }
}
