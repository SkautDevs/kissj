<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Middleware\TicketTransferCsrfMiddleware;
use kissj\Participant\ParticipantController;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class OwnerTicketTransferByTieCodeTest extends AppTestCase
{
    public function testTransferResolvesRecipientByTieCode(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->makeParticipant($app, $event, UserStatus::Paid);
        $recipient = $this->makeParticipant($app, $event, UserStatus::Approved);
        $recipientCode = $this->tieCodeOf($app, $recipient);

        $controller = $this->getService($app, ParticipantController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody(['tieCode' => $recipientCode]);
        $response = $controller->transferTicket($request, new Response(), $giver);

        self::assertSame(302, $response->getStatusCode());
        self::assertContains(
            $this->trans($app, 'flash.success.ticketTransferred'),
            $this->flashed($app),
        );

        $userRepository = $this->getService($app, UserRepository::class);
        self::assertSame(UserStatus::Open, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Paid, $userRepository->get($recipient->id)->status);
    }

    public function testTransferMatchesTieCodeCaseInsensitively(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->makeParticipant($app, $event, UserStatus::Paid);
        $recipient = $this->makeParticipant($app, $event, UserStatus::Approved);
        $recipientCode = $this->tieCodeOf($app, $recipient);

        $controller = $this->getService($app, ParticipantController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody(['tieCode' => strtolower($recipientCode)]);
        $response = $controller->transferTicket($request, new Response(), $giver);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(UserStatus::Paid, $this->getService($app, UserRepository::class)->get($recipient->id)->status);
    }

    public function testTransferFailsForUnknownTieCode(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->makeParticipant($app, $event, UserStatus::Paid);

        $controller = $this->getService($app, ParticipantController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody(['tieCode' => 'ZZZZZZ']);
        $response = $controller->transferTicket($request, new Response(), $giver);

        self::assertSame(302, $response->getStatusCode());
        self::assertContains(
            $this->trans($app, 'flash.error.transferFailed'),
            $this->flashed($app),
        );
        self::assertSame(UserStatus::Paid, $this->getService($app, UserRepository::class)->get($giver->id)->status);
    }

    public function testTransferFailsForEmptyTieCode(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->makeParticipant($app, $event, UserStatus::Paid);

        $controller = $this->getService($app, ParticipantController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody(['tieCode' => '']);
        $response = $controller->transferTicket($request, new Response(), $giver);

        self::assertSame(302, $response->getStatusCode());
        self::assertContains(
            $this->trans($app, 'flash.error.transferFailed'),
            $this->flashed($app),
        );
        self::assertSame(UserStatus::Paid, $this->getService($app, UserRepository::class)->get($giver->id)->status);
    }

    public function testFullStackPostTransferSucceedsThroughMiddleware(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->makeParticipant($app, $event, UserStatus::Paid);
        $recipient = $this->makeParticipant($app, $event, UserStatus::Approved);
        $recipientCode = $this->tieCodeOf($app, $recipient);

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);
        /** @var array<string, string> $csrf */
        $csrf = $this->getService($app, TicketTransferCsrfMiddleware::class)->generateToken();

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/participant/transferTicket',
            'POST',
            ['tieCode' => $recipientCode] + $csrf,
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('dashboard', $response->getHeaderLine('Location'));

        $userRepository = $this->getService($app, UserRepository::class);
        self::assertSame(UserStatus::Open, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Paid, $userRepository->get($recipient->id)->status);
    }

    public function testFullStackPostWithoutCsrfTokenIsRejected(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->makeParticipant($app, $event, UserStatus::Paid);
        $recipient = $this->makeParticipant($app, $event, UserStatus::Approved);
        $recipientCode = $this->tieCodeOf($app, $recipient);

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/participant/transferTicket',
            'POST',
            ['tieCode' => $recipientCode],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('showTransferTicket', $response->getHeaderLine('Location'));
        self::assertContains(
            $this->trans($app, 'flash.error.formExpired'),
            $this->flashed($app),
        );

        $userRepository = $this->getService($app, UserRepository::class);
        self::assertSame(UserStatus::Paid, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Approved, $userRepository->get($recipient->id)->status);
    }

    public function testFullStackPostWithInvalidCsrfTokenIsRejected(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->makeParticipant($app, $event, UserStatus::Paid);
        $recipient = $this->makeParticipant($app, $event, UserStatus::Approved);
        $recipientCode = $this->tieCodeOf($app, $recipient);

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            '/v2/event/' . $event->slug . '/participant/transferTicket',
            'POST',
            [
                'tieCode' => $recipientCode,
                'csrf_name' => 'csrf_forged',
                'csrf_value' => base64_encode(random_bytes(64)),
            ],
        ));

        self::assertSame(302, $response->getStatusCode());
        self::assertStringContainsString('showTransferTicket', $response->getHeaderLine('Location'));
        self::assertContains(
            $this->trans($app, 'flash.error.formExpired'),
            $this->flashed($app),
        );

        $userRepository = $this->getService($app, UserRepository::class);
        self::assertSame(UserStatus::Paid, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Approved, $userRepository->get($recipient->id)->status);
    }

    public function testTransferFormCarriesCsrfToken(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->makeParticipant($app, $event, UserStatus::Paid);
        $recipient = $this->makeParticipant($app, $event, UserStatus::Approved);
        $recipientCode = $this->tieCodeOf($app, $recipient);

        $_SESSION['user'] = ['id' => $giver->id];
        $app = $this->getTestApp(false);

        $response = $app->handle(
            $this->createRequest('/v2/event/' . $event->slug . '/participant/showTransferTicket')
                ->withQueryParams(['tieCode' => $recipientCode]),
        );

        $body = (string) $response->getBody();
        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('name="csrf_name"', $body);
        self::assertStringContainsString('name="csrf_value"', $body);
        self::assertMatchesRegularExpression('/name="csrf_value" value="[^"]+"/', $body);
    }

    public function testTransferFailsGracefullyWhenGiverHasNoPaidPayment(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        // a giver marked Paid but WITHOUT any payment row is refused by the eligibility check, not by a 500
        $giver = $this->makePaidParticipantWithoutPayment($app, $event);
        $recipient = $this->makeParticipant($app, $event, UserStatus::Approved);
        $recipientCode = $this->tieCodeOf($app, $recipient);

        $controller = $this->getService($app, ParticipantController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody(['tieCode' => $recipientCode]);
        $response = $controller->transferTicket($request, new Response(), $giver);

        self::assertSame(302, $response->getStatusCode());
        self::assertContains(
            $this->trans($app, 'flash.error.transferFailed'),
            $this->flashed($app),
        );

        $userRepository = $this->getService($app, UserRepository::class);
        // the refused transfer must not have moved statuses
        self::assertSame(UserStatus::Approved, $userRepository->get($recipient->id)->status);
    }

    public function testPostFailureDoesNotDuplicateDetailedWarnings(): void
    {
        $app = $this->getTestApp();
        $event = $this->getOwnerTransferEvent($app);

        $giver = $this->makeParticipant($app, $event, UserStatus::Paid);
        // ineligible recipient: same role but already Paid → isPaid warning would fire
        $recipient = $this->makeParticipant($app, $event, UserStatus::Paid);
        $recipientCode = $this->tieCodeOf($app, $recipient);

        $controller = $this->getService($app, ParticipantController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody(['tieCode' => $recipientCode]);
        $response = $controller->transferTicket($request, new Response(), $giver);

        self::assertSame(302, $response->getStatusCode());
        $flashed = $this->flashed($app);
        self::assertContains($this->trans($app, 'flash.error.transferFailed'), $flashed);
        // the POST must not emit the detailed eligibility warnings (the GET preview covers them)
        self::assertNotContains($this->trans($app, 'flash.warning.isPaid'), $flashed);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function makePaidParticipantWithoutPayment(App $app, Event $event): User
    {
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);

        $user = $userService->registerEmailUser('transfer-tie-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ist');
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function makeParticipant(App $app, Event $event, UserStatus $status): User
    {
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);

        $user = $userService->registerEmailUser('transfer-tie-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ist');

        if ($status === UserStatus::Paid) {
            // a Paid participant must be backed by a real confirmed payment —
            // PaymentTransferService looks up getFirstPaidPayment() on the giver
            $this->initializeMailerSettings($app, $event);
            $participantRepository = $this->getService($app, ParticipantRepository::class);
            $paymentService = $this->getService($app, PaymentService::class);
            $participant = $participantRepository->getParticipantFromUser($user);
            $payment = $paymentService->createAndPersistNewEventPayment($participant);
            $paymentService->confirmPayment($payment);

            return $userRepository->get($user->id);
        }

        $user->status = $status;
        $userRepository->persist($user);

        return $user;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function tieCodeOf(App $app, User $user): string
    {
        return $this->getService($app, ParticipantRepository::class)
            ->getParticipantFromUser($user)
            ->tieCode;
    }

    /**
     * @param App<ContainerInterface> $app
     * @return list<string>
     */
    private function flashed(App $app): array
    {
        return array_column(
            $this->getService($app, FlashMessagesBySession::class)->dumpMessagesIntoArray(),
            'message',
        );
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function trans(App $app, string $key): string
    {
        return $this->getService($app, TranslatorInterface::class)->trans($key);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function routedRequest(App $app, Event $event): ServerRequestInterface
    {
        $path = '/v2/event/' . $event->slug . '/participant/transferTicket';

        return $this->createRequest($path, 'POST')
            ->withAttribute(RouteContext::ROUTE_PARSER, $app->getRouteCollector()->getRouteParser())
            ->withAttribute(RouteContext::ROUTING_RESULTS, $app->getRouteResolver()->computeRoutingResults($path, 'POST'))
            ->withAttribute('event', $event);
    }
}
