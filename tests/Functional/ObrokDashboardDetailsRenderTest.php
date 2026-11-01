<?php

declare(strict_types=1);

namespace Tests\Functional;

use Closure;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\User\UserService;
use Tests\AppTestCase;

class ObrokDashboardDetailsRenderTest extends AppTestCase
{
    private const string BASE_URL = '/v2/event/obrok37';

    public function testIstDashboardShowsPrintedHandbookYes(): void
    {
        $body = $this->renderIstDashboard(static function (Participant $participant): void {
            $participant->printedHandbook = true;
        });

        self::assertMatchesRegularExpression('~Tištěný handbook: <b>\s*ano</b>~', $body);
    }

    public function testIstDashboardShowsPrintedHandbookNo(): void
    {
        $body = $this->renderIstDashboard(static function (Participant $participant): void {
            $participant->printedHandbook = false;
        });

        self::assertMatchesRegularExpression('~Tištěný handbook: <b>\s*ne</b>~', $body);
    }

    public function testIstDashboardShowsPrintedHandbookNoWhenUnset(): void
    {
        $body = $this->renderIstDashboard(static function (Participant $participant): void {
            $participant->printedHandbook = null;
        });

        self::assertMatchesRegularExpression('~Tištěný handbook: <b>\s*ne</b>~', $body);
    }

    public function testIstDashboardShowsPreferredPosition(): void
    {
        $body = $this->renderIstDashboard(static function (Participant $participant): void {
            $participant->preferredPosition = ['detail.position.security'];
        });

        self::assertMatchesRegularExpression('~Preferovaná pozice: <b>\s*Hlídání areálu\s*</b>~', $body);
    }

    private function renderIstDashboard(Closure $setUpParticipant): string
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('obrok-dashboard-' . uniqid('', true) . '@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');
        $setUpParticipant($participant);
        $this->getService($app, ParticipantRepository::class)->persist($participant);

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(self::BASE_URL . '/participant/dashboard'));

        self::assertSame(200, $response->getStatusCode());

        return (string) $response->getBody();
    }
}
