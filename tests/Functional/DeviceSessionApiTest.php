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
use kissj\Programme\Image\ProgrammeImage;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Place\ProgrammePlaceService;
use kissj\Programme\Programme;
use kissj\Programme\ProgrammeRegistration;
use kissj\Programme\ProgrammeRegistrationRepository;
use kissj\Programme\ProgrammeRepository;
use kissj\Programme\ProgrammeSection;
use kissj\User\UserRepository;
use kissj\User\UserStatus;
use LogicException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use Slim\Psr7\Uri;
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
        self::assertSame(['id' => $participant->id, 'nickname' => 'Session Jana'], $body['participant']);
        self::assertIsArray($body['programmes']);
        self::assertCount(1, $body['programmes']);
        $listed = $body['programmes'][0];
        self::assertIsArray($listed);
        self::assertSame($programme->id, $listed['id']);
        self::assertSame('session programme', $listed['name']);
    }

    public function testParticipantReadCarriesPlaceIdAndImagesButNoPlaces(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Placed');
        $programme = $this->createProgramme($app, $event, $this->createSection($app, $event, false));
        $place = $this->getService($app, ProgrammePlaceService::class)->findOrCreateByName($event, 'session place');
        $this->getService($app, ProgrammePlaceService::class)->assignPlace($programme, $place);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);
        $filename = str_repeat('c', 32);
        $image = new ProgrammeImage();
        $image->programme = $programme;
        $image->filename = $filename;
        $image->thumbFilename = null;
        $image->alt = 'Ohniště';
        $image->sortOrder = 0;
        $this->getService($app, ProgrammeImageRepository::class)->persist($image);
        $this->register($app, $programme, $participant);
        $request = $this->createApiRequest('/v3/programme/participant/session', self::API_KEY)
            ->withUri(new Uri('https', 'kissj.example', 443, '/v3/programme/participant/session'))
            ->withHeader(self::SESSION_HEADER, $this->createSession($app, $participant));

        $response = $app->handle($request);

        self::assertSame(200, $response->getStatusCode());
        $body = $this->decodeJson($response);
        self::assertFalse(array_key_exists('places', $body));
        self::assertIsArray($body['programmes']);
        self::assertCount(1, $body['programmes']);
        $listed = $body['programmes'][0];
        self::assertIsArray($listed);
        self::assertSame('session place', $listed['place']);
        self::assertSame($place->id, $listed['placeId']);
        self::assertSame([[
            'url' => 'https://kissj.example/v3/programme/image/' . $filename,
            'thumbUrl' => 'https://kissj.example/v3/programme/image/' . $filename . '/thumb',
            'alt' => 'Ohniště',
        ]], $listed['images']);
    }

    public function testSessionReadOrdersProgrammesByStartAndLeavesOutSoftDeletedRegistrations(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $section = $this->createSection($app, $event, false);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Ordered');
        $later = $this->createProgramme($app, $event, $section, start: '2027-06-04 10:00');
        $earlier = $this->createProgramme($app, $event, $section, start: '2027-06-04 08:00');
        $removed = $this->createProgramme($app, $event, $section, start: '2027-06-04 09:00');
        $this->register($app, $later, $participant);
        $this->register($app, $earlier, $participant);
        $this->getService($app, ProgrammeRegistrationRepository::class)
            ->softDelete($this->register($app, $removed, $participant));
        $token = $this->createSession($app, $participant);

        $response = $this->handle($app, '/v3/programme/participant/session', $token);

        self::assertSame(200, $response->getStatusCode());
        $body = $this->decodeJson($response);
        self::assertIsArray($body['programmes']);
        self::assertSame([$earlier->id, $later->id], array_column($body['programmes'], 'id'));
        $earlierProgramme = $body['programmes'][0];
        self::assertIsArray($earlierProgramme);
        self::assertSame('2027-06-04T08:00:00+02:00', $earlierProgramme['start']);
        self::assertSame($section->id, $earlierProgramme['sectionId']);
    }

    public function testSessionReadSendsEmptyNicknameAsNull(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $participant = $this->createPaidIstOnEvent($app, $event, '');
        $token = $this->createSession($app, $participant);

        $response = $this->handle($app, '/v3/programme/participant/session', $token);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            ['participant' => ['id' => $participant->id, 'nickname' => null], 'programmes' => []],
            $this->decodeJson($response),
        );
    }

    public function testSessionReadQueryCountDoesNotGrowWithRegistrations(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $sections = [$this->createSection($app, $event, false), $this->createSection($app, $event, false)];
        $participant = $this->createPaidIstOnEvent($app, $event, 'Counted');
        $token = $this->createSession($app, $participant);
        $queryCounter = $this->attachQueryCounter($app);
        $request = $this->createApiRequest('/v3/programme/participant/session', self::API_KEY)
            ->withHeader(self::SESSION_HEADER, $token);

        foreach ([0, 1] as $index) {
            $this->register(
                $app,
                $this->createProgramme($app, $event, $sections[$index], start: '2027-06-07 08:00'),
                $participant,
            );
        }
        $queriesWithTwoRegistrations = $this->countQueriesOf($app, $queryCounter, $request);

        foreach ([2, 3, 4] as $index) {
            $this->register(
                $app,
                $this->createProgramme($app, $event, $sections[$index % 2], start: '2027-06-07 09:00'),
                $participant,
            );
        }
        $queriesWithFiveRegistrations = $this->countQueriesOf($app, $queryCounter, $request);

        $response = $app->handle($request);
        self::assertSame(200, $response->getStatusCode());
        $programmes = $this->decodeJson($response)['programmes'];
        self::assertIsArray($programmes);
        self::assertCount(5, $programmes);
        self::assertSame($queriesWithTwoRegistrations, $queriesWithFiveRegistrations);
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

    public function testRevokeDeletesTheSession(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Revoked');
        $token = $this->createSession($app, $participant);
        $otherDeviceToken = $this->createSession($app, $participant);
        $otherParticipantToken = $this->createSession($app, $this->createPaidIstOnEvent($app, $event, 'Untouched'));

        $response = $this->handle($app, '/v3/programme/session/revoke', $token, 'POST');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(['ok' => true], $this->decodeJson($response));
        foreach (
            [
                $this->handle($app, '/v3/programme/participant/session', $token),
                $this->handle($app, '/v3/programme/session/revoke', $token, 'POST'),
            ] as $afterRevoke
        ) {
            self::assertSame(401, $afterRevoke->getStatusCode());
            self::assertSame('', (string)$afterRevoke->getBody());
        }
        foreach ([$otherDeviceToken, $otherParticipantToken] as $untouchedToken) {
            self::assertSame(
                200,
                $this->handle($app, '/v3/programme/participant/session', $untouchedToken)->getStatusCode(),
            );
        }
    }

    public function testRevokedSessionIsNotReissuedByItsPairing(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $participant = $this->createPaidIstOnEvent($app, $event, 'Reissue');
        $pairing = $this->createDevicePairing($app, $event);
        $this->getService($app, DevicePairingService::class)->approveEnteredCode(
            $pairing->userCode,
            $pairing->event,
            $participant,
        );
        $approved = $this->decodeJson($this->poll($app, $pairing->deviceCode));
        self::assertSame('approved', $approved['status']);
        $token = $approved['sessionToken'];
        self::assertIsString($token);

        self::assertSame(200, $this->handle($app, '/v3/programme/session/revoke', $token, 'POST')->getStatusCode());
        $pairingRepository = $this->getService($app, DevicePairingRepository::class);
        $stored = $pairingRepository->get($pairing->id);
        $stored->lastPolledAt = DateTimeUtils::getDateTime('-6 seconds');
        $pairingRepository->persist($stored);
        $late = $this->poll($app, $pairing->deviceCode);

        self::assertSame('{"status":"expired"}', (string)$late->getBody());
        self::assertCount(
            0,
            $this->getService($app, DeviceSessionRepository::class)->findBy(['participant' => $participant]),
        );
    }

    public function testPatrolMemberSessionReadsTheMembersProgrammes(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $member = $this->createPatrolMemberOnEvent($app, $event, 'Vlk');
        $programme = $this->createProgramme($app, $event, $this->createSection($app, $event, false));
        $this->register($app, $programme, $member);
        $token = $this->createSession($app, $member);

        $response = $this->handle($app, '/v3/programme/participant/session', $token);

        self::assertSame(200, $response->getStatusCode());
        $body = $this->decodeJson($response);
        self::assertSame(['id' => $member->id, 'nickname' => 'Vlk'], $body['participant']);
        self::assertIsArray($body['programmes']);
        self::assertSame([$programme->id], array_column($body['programmes'], 'id'));
    }

    public function testPatrolMemberSessionOfCancelledLeaderIs401(): void
    {
        $app = $this->getTestApp();
        $event = $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);
        $member = $this->createPatrolMemberOnEvent($app, $event, 'Liška');
        $token = $this->createSession($app, $member);
        $this->setUserStatus($app, $member, UserStatus::Cancelled);

        $response = $this->handle($app, '/v3/programme/participant/session', $token);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('', (string)$response->getBody());
    }

    public function testRevokeWithoutSessionIs401(): void
    {
        $app = $this->getTestApp();
        $this->authorizeEventForApi($app, self::EVENT_SLUG, self::API_KEY);

        $response = $this->handle($app, '/v3/programme/session/revoke', '', 'POST');

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
        $this->getService($app, DevicePairingService::class)->approveEnteredCode(
            $pairing->userCode,
            $pairing->event,
            $participant,
        );

        $pollRequest = $this->createApiRequest('/v3/programme/pairing/poll', self::API_KEY, 'POST');
        $pollRequest->getBody()->write(json_encode(['deviceCode' => $deviceCode], JSON_THROW_ON_ERROR));
        $poll = $this->decodeJson($app->handle($pollRequest));
        self::assertSame('approved', $poll['status']);
        $token = $poll['sessionToken'];
        self::assertIsString($token);

        $read = $this->handle($app, '/v3/programme/participant/session', $token);
        self::assertSame(200, $read->getStatusCode());
        self::assertSame(['id' => $participant->id, 'nickname' => 'Flow'], $this->decodeJson($read)['participant']);

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
    private function poll(App $app, string $deviceCode): Response
    {
        $request = $this->createApiRequest('/v3/programme/pairing/poll', self::API_KEY, 'POST');
        $request->getBody()->write(json_encode(['deviceCode' => $deviceCode], JSON_THROW_ON_ERROR));

        return $app->handle($request);
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
        string $start = '2027-06-03 10:00',
    ): Programme {
        $programme = new Programme();
        $programme->name = 'session programme';
        $programme->description = 'description';
        $programme->start = DateTimeUtils::getDateTime($start);
        $programme->end = DateTimeUtils::getDateTime($start . ' +2 hours');
        $programme->capacity = $capacity;
        $programme->isPreregistered = $preregistered;
        $programme->event = $event;
        $programme->section = $section;
        $programme->setTargetRoles($roles);
        $this->getService($app, ProgrammeRepository::class)->persist($programme);

        return $programme;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function register(App $app, Programme $programme, Participant $participant): ProgrammeRegistration
    {
        $registration = new ProgrammeRegistration();
        $registration->programme = $programme;
        $registration->participant = $participant;
        $this->getService($app, ProgrammeRegistrationRepository::class)->persist($registration);

        return $registration;
    }
}
