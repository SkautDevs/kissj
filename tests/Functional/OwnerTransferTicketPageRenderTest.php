<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Slim\App;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class OwnerTransferTicketPageRenderTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    public function testTransferPageRendersTieCodeForm(): void
    {
        $app = $this->getTestApp();
        $this->flipEventToKorbo($app);

        $giver = $this->createIst($app, 'transfer-giver-' . uniqid() . '@example.com', UserStatus::Paid, 'Giver', 'One');

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/showTransferTicket'
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('name="tieCode"', $body);
        self::assertStringNotContainsString('name="emailTo"', $body);
    }

    public function testTransferPagePreviewShowsRecipientNameAndStatus(): void
    {
        $app = $this->getTestApp();
        $this->flipEventToKorbo($app);

        $giver = $this->createIst($app, 'transfer-giver-' . uniqid() . '@example.com', UserStatus::Paid, 'Giver', 'Two');
        $recipient = $this->createIst($app, 'transfer-recipient-' . uniqid() . '@example.com', UserStatus::Approved, 'Pavel', 'Recipient');

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $recipientParticipant = $participantRepository->getParticipantFromUser($recipient);
        $recipientCode = $recipientParticipant->tieCode;

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/showTransferTicket'
        )->withQueryParams(['tieCode' => $recipientCode]));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('Pavel', $body);
        self::assertStringContainsString('Recipient', $body);
    }

    public function testTransferPageShowsNotFoundForUnknownCode(): void
    {
        $app = $this->getTestApp();
        $this->flipEventToKorbo($app);

        $giver = $this->createIst($app, 'transfer-giver-' . uniqid() . '@example.com', UserStatus::Paid, 'Giver', 'Three');

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/showTransferTicket'
        )->withQueryParams(['tieCode' => 'ZZZZZZ']));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $translator = $this->getService($app, TranslatorInterface::class);
        self::assertStringContainsString(
            $translator->trans('ticketTransfer.recipientNotFound'),
            $body,
        );
        // the admin-flow "one or both participants not found" flash is misleading here
        self::assertStringNotContainsString(
            $translator->trans('flash.warning.nullParticipants'),
            $body,
        );
    }

    public function testTransferPageShowsNotPossibleForIneligibleRecipient(): void
    {
        $app = $this->getTestApp();
        $this->flipEventToKorbo($app);

        $giver = $this->createIst($app, 'transfer-giver-' . uniqid() . '@example.com', UserStatus::Paid, 'Giver', 'Four');
        $recipient = $this->createIst($app, 'transfer-recipient-' . uniqid() . '@example.com', UserStatus::Paid, 'Pavel', 'Recipient');

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $recipientCode = $participantRepository->getParticipantFromUser($recipient)->tieCode;

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/showTransferTicket'
        )->withQueryParams(['tieCode' => $recipientCode]));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('Pavel', $body);
        self::assertStringContainsString(
            $this->getService($app, TranslatorInterface::class)->trans('ticketTransfer.notPossible'),
            $body,
        );
    }

    public function testDashboardShowsOwnTieCodeOnOwnerTransferEvent(): void
    {
        $app = $this->getTestApp();
        $this->flipEventToKorbo($app);

        $user = $this->createIst($app, 'transfer-dashboard-' . uniqid() . '@example.com', UserStatus::Approved, 'Dashboard', 'One');

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $tieCode = $participantRepository->getParticipantFromUser($user)->tieCode;

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/dashboard'
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString($tieCode, $body);
    }

    public function testDashboardHidesTieCodeWhenOwnerTransferNotAllowed(): void
    {
        $app = $this->getTestApp();

        $user = $this->createIst($app, 'transfer-dashboard-' . uniqid() . '@example.com', UserStatus::Approved, 'Dashboard', 'Two');

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $tieCode = $participantRepository->getParticipantFromUser($user)->tieCode;

        $_SESSION['user'] = ['id' => $user->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/dashboard'
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringNotContainsString($tieCode, $body);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function flipEventToKorbo(App $app): void
    {
        $this->getService($app, Connection::class)->query(
            'UPDATE event SET event_type = %s WHERE slug = %s',
            'korbo',
            self::TEST_EVENT_SLUG,
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createIst(App $app, string $email, UserStatus $status, string $firstName, string $lastName): User
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            $event = $eventRepository->get(1);
        }

        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        /** @var Ist $ist */
        $ist = $istRepository->get($participant->id);
        $ist->firstName = $firstName;
        $ist->lastName = $lastName;
        $istRepository->persist($ist);

        $userRepository = $this->getService($app, UserRepository::class);
        $user->status = $status;
        $userRepository->persist($user);

        return $user;
    }
}
