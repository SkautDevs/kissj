<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Mailer\MailerSettings;
use kissj\Participant\Admin\AdminController;
use kissj\Participant\Ist\IstRepository;
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
use Tests\AppTestCase;

class AdminPaymentTransferTest extends AppTestCase
{
    public function testTransferPaymentMovesMoneyAndStatusesViaAdminController(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $this->initializeMailerSettings($app, $event);

        $userRepository = $this->getService($app, UserRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $paymentService = $this->getService($app, PaymentService::class);

        $giver = $this->createIst($app, $event);
        $giverPayment = $paymentService->createAndPersistNewEventPayment(
            $participantRepository->getParticipantFromUser($giver),
        );
        $paymentService->confirmPayment($giverPayment);

        $recipient = $this->createIst($app, $event);
        $recipient->status = UserStatus::Approved;
        $userRepository->persist($recipient);

        $controller = $this->getService($app, AdminController::class);
        $request = $this->routedRequest($app, $event)->withParsedBody([
            'emailFrom' => $giver->email,
            'emailTo' => $recipient->email,
        ]);

        $response = $controller->transferPayment($request, new Response(), $event);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame(UserStatus::Open, $userRepository->get($giver->id)->status);
        self::assertSame(UserStatus::Paid, $userRepository->get($recipient->id)->status);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createIst(App $app, Event $event): User
    {
        $userService = $this->getService($app, UserService::class);
        $istRepository = $this->getService($app, IstRepository::class);

        $user = $userService->registerEmailUser('admin-transfer-' . uniqid('', true) . '@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Admin';
        $ist->lastName = 'Transfer';
        $ist->nickname = 'AT';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $user->email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);

        return $user;
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
        $path = '/v2/event/' . $event->slug . '/admin/payments/transferPayment';

        return $this->createRequest($path, 'POST')
            ->withAttribute(RouteContext::ROUTE_PARSER, $app->getRouteCollector()->getRouteParser())
            ->withAttribute(RouteContext::ROUTING_RESULTS, $app->getRouteResolver()->computeRoutingResults($path, 'POST'))
            ->withAttribute('event', $event);
    }
}
