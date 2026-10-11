<?php

declare(strict_types=1);

namespace Tests\Functional;

use ArrayObject;
use kissj\Application\DateTimeUtils;
use kissj\Event\EventRepository;
use kissj\Event\EventType\Jj\EventTypeJj;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use kissj\Participant\Participant;
use kissj\Participant\TechnicalTeam\TechnicalTeamLeader;
use kissj\Participant\TechnicalTeam\TechnicalTeamLeaderRepository;
use kissj\User\UserService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Event\SentMessageEvent;
use Symfony\Component\Mime\Email;
use Tests\AppTestCase;

class TechnicalTeamLoginAndPricingTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testLoginMailLinkCarriesTtlTokenAndGrantsAccess(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->mutateEventForTest($app->getContainer(), $event, [
            'allowTechnicalTeams' => true,
            'technicalTeamRegistrationToken' => 'ttl-mail-token',
        ]);
        $email = 'ttl-mail-' . bin2hex(random_bytes(4)) . '@example.com';
        $this->getService($app, UserService::class)->registerEmailUser($email, $event);

        /** @var ArrayObject<int, string> $bodies */
        $bodies = new ArrayObject();
        $dispatcher = $this->getService($app, EventDispatcherInterface::class);
        $dispatcher->addListener(SentMessageEvent::class, static function (SentMessageEvent $sent) use ($bodies): void {
            $message = $sent->getMessage()->getOriginalMessage();
            if ($message instanceof Email) {
                $bodies[] = (string)$message->getHtmlBody() . (string)$message->getTextBody();
            }
        });

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/login',
            'POST',
            ['email' => $email, 'ttl_token' => 'ttl-mail-token'],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertCount(1, $bodies);
        self::assertSame(1, preg_match('~/tryLogin/([0-9a-f]{32})\?ttl_token=ttl-mail-token~', $bodies[0] ?? '', $matches));

        unset($_SESSION['ttl_access_granted']);
        $loginResponse = $this->getTestApp(false)->handle(
            $this->createRequest(self::BASE_URL . '/tryLogin/' . $matches[1])->withQueryParams(['ttl_token' => 'ttl-mail-token']),
        );

        self::assertSame(302, $loginResponse->getStatusCode());
        self::assertIsInt($_SESSION['ttl_access_granted']);
    }

    public function testLoginPageForwardsTtlTokenIntoEmailForm(): void
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->mutateEventForTest($app->getContainer(), $event, [
            'allowTechnicalTeams' => true,
            'technicalTeamRegistrationToken' => 'ttl-form-token',
        ]);
        $this->setEventType($app->getContainer(), 'nsj', self::TEST_EVENT_SLUG);

        $response = $this->getTestApp(false)->handle(
            $this->createRequest(self::BASE_URL . '/login')->withQueryParams(['ttl_token' => 'ttl-form-token']),
        );

        self::assertStringContainsString('name="ttl_token" value="ttl-form-token"', (string)$response->getBody());
    }

    public function testTechnicalTeamLeaderPaysNoScarfOrLateSurchargeOnKorboAndJj(): void
    {
        $leader = $this->createLeaderWithSurchargeTriggers();

        self::assertSame(100, (new EventTypeKorbo())->getPrice($leader));
        self::assertSame(100, (new EventTypeJj())->getPrice($leader));
    }

    private function createLeaderWithSurchargeTriggers(): TechnicalTeamLeader
    {
        $app = $this->getTestApp();
        $event = $this->getTestSlugEvent($this->getService($app, EventRepository::class));
        $this->mutateEventForTest($app->getContainer(), $event, [
            'allowTechnicalTeams' => true,
            'technicalTeamPrice' => 100,
        ]);
        $user = $this->getService($app, UserService::class)
            ->registerEmailUser('ttl-price-' . bin2hex(random_bytes(4)) . '@example.com', $event);
        $participant = $this->getService($app, UserService::class)->createParticipantSetRole($user, 'ttl');
        $leader = $this->getService($app, TechnicalTeamLeaderRepository::class)->get($participant->id);
        $leader->scarf = Participant::SCARF_YES;
        $leader->registrationCloseDate = DateTimeUtils::getDateTime('2099-01-01');

        return $leader;
    }
}
