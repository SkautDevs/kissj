<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Participant\ParticipantController;
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

class OwnerTransferSelfTest extends AppTestCase
{
    public function testTransferTicketRejectsTransferToYourself(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $user = $this->makePaidParticipant($app, $event);

        $controller = $this->getService($app, ParticipantController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody(['emailTo' => $user->email]);

        $response = $controller->transferTicket($request, new Response(), $user);

        self::assertSame(302, $response->getStatusCode());

        $translator = $this->getService($app, TranslatorInterface::class);
        $flashed = array_column(
            $this->getService($app, FlashMessagesBySession::class)->dumpMessagesIntoArray(),
            'message',
        );
        self::assertContains($translator->trans('flash.warning.cannotTransferToYourself'), $flashed);

        // no transfer happened — the owner is still Paid
        self::assertSame(UserStatus::Paid, $this->getService($app, UserRepository::class)->get($user->id)->status);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function makePaidParticipant(App $app, Event $event): User
    {
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);

        $user = $userService->registerEmailUser('self-transfer-' . uniqid('', true) . '@example.com', $event);
        $userService->createParticipantSetRole($user, 'ist');
        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        return $user;
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
