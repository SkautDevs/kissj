<?php

declare(strict_types=1);

namespace Tests\Functional;

use DateTimeImmutable;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Participant\Admin\PaymentTransferService;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\Payment\PaymentStatus;
use kissj\Programme\Device\DevicePairingRepository;
use kissj\Programme\Device\DevicePairingService;
use kissj\Programme\Device\DeviceSession;
use kissj\Programme\Device\DeviceSessionRepository;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use Tests\AppTestCase;

class DeviceSessionApiTest extends AppTestCase
{
    private const string EVENT_SLUG = 'test-event-slug';
    private const string API_KEY = 'device-session-test-key';
    private const string OTHER_EVENT_SLUG = 'device-session-other-event';
    private const string SESSION_HEADER = 'X-Kissj-Device-Session';

    public function testSessionReadReturnsNicknameAndRegistrations(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Session Jana');
        $programme = $this->createProgramme($app, $event, $this->createSection($app, $event, false));
        $token = $this->createSession($app, $participant);
        $this->openProgrammeRegistrationWindow($app, self::EVENT_SLUG);
        $this->handle($app, '/v3/programme/' . $programme->id . '/register', $token, 'POST');

        $response = $this->handle($app, '/v3/programme/participant/session', $token);

        self::assertSame(200, $response->getStatusCode());
        $body = $this->decodeJson($response);
        self::assertSame(['nickname' => 'Session Jana'], $body['participant']);
        self::assertIsArray($body['programmes']);
        self::assertCount(1, $body['programmes']);
        $listed = $body['programmes'][0];
        self::assertIsArray($listed);
        self::assertSame($programme->id, $listed['id']);
        self::assertSame('session programme', $listed['name']);
    }

    public function testSessionOfTransferSenderStaysValidWithNoProgrammes(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $this->openProgrammeRegistrationWindow($app, self::EVENT_SLUG);
        $paymentService = $this->getService($app, PaymentService::class);
        $sender = $this->createPaidIstOnEvent($app, $event, 'Sender');
        $payment = $paymentService->createAndPersistNewEventPayment($sender);
        $payment->status = PaymentStatus::Paid;
        $this->getService($app, PaymentRepository::class)->persist($payment);
        $recipient = $this->createPaidIstOnEvent($app, $event, 'Recipient');
        $this->setUserStatus($app, $recipient, UserStatus::Approved);
        $programme = $this->createProgramme($app, $event, $this->createSection($app, $event, false));
        $token = $this->createSession($app, $sender);
        self::assertSame(
            200,
            $this->handle($app, '/v3/programme/' . $programme->id . '/register', $token, 'POST')->getStatusCode(),
        );

        $this->initializeMailerSettings($app, $event);
        $this->captureSentMessageRecipients($app);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $this->getService($app, PaymentTransferService::class)->transferPayment(
            $participantRepository->getParticipantFromUser($sender->getUserButNotNull()),
            $participantRepository->getParticipantFromUser($recipient->getUserButNotNull()),
        );

        $response = $this->handle($this->getTestApp(false), '/v3/programme/participant/session', $token);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $this->decodeJson($response)['programmes']);
    }

    public function testMissingUnknownAndExpiredSessionIs401(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Expired');
        $expired = $this->createSession($app, $participant, DateTimeUtils::getDateTime('-1 minute'));

        $missing = $app->handle($this->createApiRequest('/v3/programme/participant/session', self::API_KEY));
        $empty = $this->handle($app, '/v3/programme/participant/session', '');
        $unknown = $this->handle($app, '/v3/programme/participant/session', bin2hex(random_bytes(32)));
        $expiredResponse = $this->handle($app, '/v3/programme/participant/session', $expired);

        foreach ([$missing, $empty, $unknown, $expiredResponse] as $response) {
            self::assertSame(401, $response->getStatusCode());
            self::assertSame('', (string)$response->getBody());
        }
    }

    public function testCancelledParticipantSessionIs401(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Cancelled');
        $token = $this->createSession($app, $participant);
        $this->setUserStatus($app, $participant, UserStatus::Cancelled);

        $response = $this->handle($app, '/v3/programme/participant/session', $token);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
    }

    public function testSessionOfAnotherEventIs401(): void
    {
        $app = $this->getTestApp();
        $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $otherEvent = $this->getTypedTestEvent($app, 'default', self::OTHER_EVENT_SLUG);
        $participant = $this->createPaidIstOnEvent($app, $otherEvent, 'Foreign');
        $expiresAt = DateTimeUtils::getDateTime('+1 month');
        $token = $this->createSession($app, $participant, $expiresAt);

        $response = $this->handle($app, '/v3/programme/participant/session', $token);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
        self::assertSame($expiresAt->getTimestamp(), $this->findSession($app, $token)->expiresAt->getTimestamp());
    }

    public function testSessionIsExtendedOnlyWhenMoreThanADayShort(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Extended');
        $dueToken = $this->createSession($app, $participant, DateTimeUtils::getDateTime('+5 months'));
        $freshExpiry = DateTimeUtils::getDateTime('+6 months -1 hour');
        $freshToken = $this->createSession($app, $participant, $freshExpiry);

        self::assertSame(200, $this->handle($app, '/v3/programme/participant/session', $dueToken)->getStatusCode());
        self::assertSame(200, $this->handle($app, '/v3/programme/participant/session', $freshToken)->getStatusCode());

        self::assertEqualsWithDelta(
            DateTimeUtils::getDateTime('+6 months')->getTimestamp(),
            $this->findSession($app, $dueToken)->expiresAt->getTimestamp(),
            60,
        );
        self::assertSame(
            $freshExpiry->getTimestamp(),
            $this->findSession($app, $freshToken)->expiresAt->getTimestamp(),
        );
    }

    public function testRegisterAndUnregisterOk(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $this->openProgrammeRegistrationWindow($app, self::EVENT_SLUG);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Registers');
        $programme = $this->createProgramme($app, $event, $this->createSection($app, $event, false));
        $token = $this->createSession($app, $participant);
        $registrationRepository = $this->getService($app, ProgrammeRegistrationRepository::class);

        $register = $this->handle($app, '/v3/programme/' . $programme->id . '/register', $token, 'POST');

        self::assertSame(200, $register->getStatusCode());
        self::assertSame('application/json', $register->getHeaderLine('Content-Type'));
        self::assertSame('{"ok":true}', (string)$register->getBody());
        self::assertNotNull($registrationRepository->findActive($programme, $participant));

        $unregister = $this->handle($app, '/v3/programme/' . $programme->id . '/unregister', $token, 'POST');

        self::assertSame(200, $unregister->getStatusCode());
        self::assertSame('{"ok":true}', (string)$unregister->getBody());
        self::assertNull($registrationRepository->findActive($programme, $participant));
    }

    public function testEveryErrorCode(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Errors');
        $token = $this->createSession($app, $participant);
        $section = $this->createSection($app, $event, true);
        $plain = $this->createProgramme($app, $event, $section);

        $this->assertError($app, 'closed', '/v3/programme/' . $plain->id . '/register', $token);
        $this->assertError($app, 'closed', '/v3/programme/' . $plain->id . '/unregister', $token);

        $this->openProgrammeRegistrationWindow($app, self::EVENT_SLUG);
        $preregistered = $this->createProgramme($app, $event, $section, preregistered: true);
        $guestOnly = $this->createProgramme($app, $event, $section, roles: [ParticipantRole::Guest]);
        $deleted = $this->createProgramme($app, $event, $section);
        $deleted->deletedAt = DateTimeUtils::getDateTime();
        $this->getService($app, ProgrammeRepository::class)->persist($deleted);
        $otherEvent = $this->getTypedTestEvent($app, 'default', self::OTHER_EVENT_SLUG);
        $foreign = $this->createProgramme($app, $otherEvent, $this->createSection($app, $otherEvent, false));

        $this->assertError($app, 'notFound', '/v3/programme/99999999/register', $token);
        $this->assertError($app, 'notFound', '/v3/programme/99999999/unregister', $token);
        $this->assertError($app, 'notFound', '/v3/programme/' . $foreign->id . '/register', $token);
        $this->assertError($app, 'notFound', '/v3/programme/' . $deleted->id . '/register', $token);
        $this->assertError($app, 'notFound', '/v3/programme/' . $deleted->id . '/unregister', $token);
        $this->assertError($app, 'notPossible', '/v3/programme/' . $preregistered->id . '/register', $token);
        $this->assertError($app, 'notPossible', '/v3/programme/' . $preregistered->id . '/unregister', $token);
        $this->assertError($app, 'wrongRole', '/v3/programme/' . $guestOnly->id . '/register', $token);
        $this->assertError($app, 'notRegistered', '/v3/programme/' . $plain->id . '/unregister', $token);

        self::assertSame(
            200,
            $this->handle($app, '/v3/programme/' . $plain->id . '/register', $token, 'POST')->getStatusCode(),
        );
        $this->assertError($app, 'alreadyRegistered', '/v3/programme/' . $plain->id . '/register', $token);

        $overlapping = $this->createProgramme($app, $event, $section);
        $this->assertError($app, 'conflict', '/v3/programme/' . $overlapping->id . '/register', $token);

        $otherSection = $this->createSection($app, $event, false);
        $capped = $this->createProgramme($app, $event, $otherSection, capacity: 1);
        $other = $this->createPaidIstOnEvent($app, $event, 'Takes the spot');
        $otherToken = $this->createSession($app, $other);
        self::assertSame(
            200,
            $this->handle($app, '/v3/programme/' . $capped->id . '/register', $otherToken, 'POST')->getStatusCode(),
        );
        $this->assertError($app, 'full', '/v3/programme/' . $capped->id . '/register', $token);

        $this->setUserStatus($app, $participant, UserStatus::Open);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'programmeRegistrationStart' => DateTimeUtils::getDateTime('-2 hours'),
            'programmeRegistrationEnd' => DateTimeUtils::getDateTime('-1 hour'),
        ]);
        $this->assertError($app, 'notPaid', '/v3/programme/' . $capped->id . '/register', $token);
        $this->openProgrammeRegistrationWindow($app, self::EVENT_SLUG);
        $this->assertError($app, 'notPaid', '/v3/programme/' . $capped->id . '/register', $token);
        $this->assertError($app, 'notPaid', '/v3/programme/' . $plain->id . '/unregister', $token);
    }

    public function testWriteWithoutSessionIs401BeforeAnyCode(): void
    {
        $app = $this->getTestApp();
        $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);

        $response = $app->handle($this->createApiRequest('/v3/programme/1/register', self::API_KEY, 'POST'));

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
    }

    public function testFullFlowFromStartToRegister(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $this->openProgrammeRegistrationWindow($app, self::EVENT_SLUG);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Flow');
        $programme = $this->createProgramme($app, $event, $this->createSection($app, $event, false));

        $start = $app->handle($this->createPairingStartRequest(self::API_KEY));
        $deviceCode = $this->decodeJson($start)['deviceCode'];
        self::assertIsString($deviceCode);
        $pairing = $this->getService($app, DevicePairingRepository::class)->findByDeviceCode($deviceCode, $event)
            ?? throw new LogicException('started pairing not found');
        $this->getService($app, DevicePairingService::class)->approve($pairing, $participant);

        $pollRequest = $this->createApiRequest('/v3/programme/pairing/poll', self::API_KEY, 'POST');
        $pollRequest->getBody()->write(json_encode(['deviceCode' => $deviceCode], JSON_THROW_ON_ERROR));
        $poll = $this->decodeJson($app->handle($pollRequest));
        self::assertSame('approved', $poll['status']);
        $token = $poll['sessionToken'];
        self::assertIsString($token);

        $read = $this->handle($app, '/v3/programme/participant/session', $token);
        self::assertSame(200, $read->getStatusCode());
        self::assertSame(['nickname' => 'Flow'], $this->decodeJson($read)['participant']);

        $register = $this->handle($app, '/v3/programme/' . $programme->id . '/register', $token, 'POST');
        self::assertSame('{"ok":true}', (string)$register->getBody());
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function assertError(App $app, string $code, string $path, string $token): void
    {
        $response = $this->handle($app, $path, $token, 'POST');

        self::assertSame(400, $response->getStatusCode(), $code . ' on ' . $path);
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame('{"error":"' . $code . '"}', (string)$response->getBody(), $path);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function handle(App $app, string $path, string $token, string $method = 'GET'): Response
    {
        return $app->handle(
            $this->createApiRequest($path, self::API_KEY, $method)->withHeader(self::SESSION_HEADER, $token),
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createSession(App $app, Participant $participant, ?DateTimeImmutable $expiresAt = null): string
    {
        $token = bin2hex(random_bytes(32));
        $session = new DeviceSession();
        $session->participant = $participant;
        $session->tokenHash = DeviceSession::hashToken($token);
        $session->expiresAt = $expiresAt ?? DateTimeUtils::getDateTime('+6 months');
        $this->getService($app, DeviceSessionRepository::class)->persist($session);

        return $token;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function findSession(App $app, string $token): DeviceSession
    {
        $tokenHash = DeviceSession::hashToken($token);

        return $this->getService($app, DeviceSessionRepository::class)->findByTokenHash($tokenHash)
            ?? throw new LogicException('session not found');
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function setUserStatus(App $app, Participant $participant, UserStatus $status): void
    {
        $user = $participant->getUserButNotNull();
        $user->status = $status;
        $this->getService($app, UserRepository::class)->persist($user);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createSection(App $app, Event $event, bool $exclusive): ProgrammeSection
    {
        return $this->createProgrammeSection($app->getContainer(), $event, 'session section', $exclusive);
    }

    /**
     * @param App<ContainerInterface> $app
     * @param list<ParticipantRole> $roles
     */
    private function createProgramme(
        App $app,
        Event $event,
        ProgrammeSection $section,
        bool $preregistered = false,
        array $roles = [ParticipantRole::Ist],
        int $capacity = 10,
    ): Programme {
        $programme = new Programme();
        $programme->name = 'session programme';
        $programme->description = 'description';
        $programme->place = 'main field';
        $programme->imageFilename = null;
        $programme->start = DateTimeUtils::getDateTime('2027-06-03 10:00');
        $programme->end = DateTimeUtils::getDateTime('2027-06-03 12:00');
        $programme->capacity = $capacity;
        $programme->isPreregistered = $preregistered;
        $programme->event = $event;
        $programme->section = $section;
        $programme->setTargetRoles($roles);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

}
