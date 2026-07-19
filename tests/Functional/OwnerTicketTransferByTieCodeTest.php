<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\MailerSettings;
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
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class OwnerTicketTransferByTieCodeTest extends AppTestCase
{
    public function testTransferResolvesRecipientByTieCode(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);

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
        $event = $this->getService($app, EventRepository::class)->get(1);

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
        $event = $this->getService($app, EventRepository::class)->get(1);

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
    private function initializeMailerSettings(App $app, Event $event): void
    {
        $mailerSettings = $this->getService($app, MailerSettings::class);
        $mailerSettings->setEvent($event);
        $mailerSettings->setFullUrlLink('http://test.example.com/v2/event/' . $event->slug);

        $view = $this->getService($app, Twig::class);
        $view->getEnvironment()->addGlobal('event', $event);
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
