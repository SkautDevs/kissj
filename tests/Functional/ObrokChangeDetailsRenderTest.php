<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Participant\Troop\TroopLeaderRepository;
use kissj\User\UserService;
use Tests\AppTestCase;

class ObrokChangeDetailsRenderTest extends AppTestCase
{
    private const string BASE_URL = '/v2/event/obrok37';

    public function testIstFormRendersHelpTexts(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('obrok-helptext-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ist');

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(self::BASE_URL . '/participant/showChangeDetails'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('dostanou vrácenou část poplatku', $body);
        self::assertStringContainsString('Chceš dělat buddyho zahraničním servisákům?', $body);
        self::assertStringContainsString('Bereš s sebou děti do školky?', $body);
        self::assertStringContainsString('ano, angličtina B1, němčina pasivně', $body);
        self::assertStringContainsString('href="https://obrok.skaut.cz/souhlas"', $body);
        self::assertStringContainsString('PDF nebo obrázek, max 10 MB', $body);
        self::assertStringContainsString('Psychická omezení', $body);
        self::assertStringContainsString('placeholder="alergie, dlouhodobé problémy"', $body);
        self::assertStringContainsString('Jiné - prosím upřesni v dovednostech', $body);
        self::assertStringContainsString('Preferuji tištěný handbook místo digitálního', $body);
        self::assertStringNotContainsString('PDF s potvrzením najdeš na našem webu', $body);
        self::assertSame(1, substr_count($body, 'v případě jiné diety'));
    }

    public function testLayoutWrapsEventNameAndMarksHelpTexts(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('obrok-layout-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ist');

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(self::BASE_URL . '/participant/showChangeDetails'));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString(
            '<h1 class="text-center">Registrace <span class="event-name">'
                . htmlspecialchars($event->readableName) . '</span></h1>',
            $body,
        );
        self::assertStringContainsString('<link rel="icon" href="/obrok27/favicon-32.png" type="image/png">', $body);
        self::assertStringContainsString(
            '<link rel="apple-touch-icon" href="/obrok27/apple-touch-icon-180.png">',
            $body,
        );
        self::assertStringContainsString(
            '<meta name="theme-color" content="#F1F1F4" media="(prefers-color-scheme: light)">',
            $body,
        );
        self::assertStringContainsString(
            '<meta name="theme-color" content="#15133F" media="(prefers-color-scheme: dark)">',
            $body,
        );
        self::assertStringContainsString('<div class="help-text">', $body);
        self::assertStringNotContainsString('<div>PDF nebo obrázek, max 10 MB', $body);
    }

    public function testAdultTroopLeaderEmergencyContactIsNotRequired(): void
    {
        $tag = $this->inputTag($this->renderTroopLeaderDetails('1990-01-01'), 'emergencyContact');

        self::assertStringContainsString('data-required="true"', $tag);
        self::assertStringNotContainsString('required="required"', $tag);
    }

    public function testMinorTroopLeaderEmergencyContactIsRequired(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $birthDate = DateTimeUtils::getDateTime($event->startDay->format('Y-m-d'))
            ->modify('-16 years')
            ->format('Y-m-d');

        $body = $this->renderTroopLeaderDetails($birthDate);

        $emergencyContact = $this->inputTag($body, 'emergencyContact');
        self::assertStringContainsString('data-required="true"', $emergencyContact);
        self::assertStringContainsString('required="required"', $emergencyContact);

        $parentalConsent = $this->inputTag($body, 'parentalConsent');
        self::assertStringNotContainsString('required', $parentalConsent);
    }

    public function testTroopLeaderWithoutBirthDateEmergencyContactIsRequired(): void
    {
        $tag = $this->inputTag($this->renderTroopLeaderDetails(null), 'emergencyContact');

        self::assertStringContainsString('data-required="true"', $tag);
        self::assertStringContainsString('required="required"', $tag);
    }

    public function testBirthDateCarriesEventStartDate(): void
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);

        $tag = $this->inputTag($this->renderTroopLeaderDetails(null), 'birthDate');

        self::assertStringContainsString('data-event-start-date="' . $event->startDay->format('Y-m-d') . '"', $tag);
    }

    private function renderTroopLeaderDetails(?string $birthDate): string
    {
        $app = $this->getTestApp();
        $event = $this->getObrokTestEvent($app);
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser('obrok-tl-' . uniqid('', true) . '@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'tl');

        if ($birthDate !== null) {
            $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);
            $troopLeader = $troopLeaderRepository->get($participant->id);
            $troopLeader->birthDate = DateTimeUtils::getDateTime($birthDate);
            $troopLeaderRepository->persist($troopLeader);
        }

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(self::BASE_URL . '/participant/showChangeDetails'));

        self::assertSame(200, $response->getStatusCode());

        return (string) $response->getBody();
    }

    private function inputTag(string $body, string $id): string
    {
        self::assertSame(1, preg_match('/<input\b[^>]*\bid="' . preg_quote($id, '/') . '"[^>]*>/s', $body, $matches));

        return $matches[0];
    }
}
