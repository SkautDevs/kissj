<?php

declare(strict_types=1);

namespace kissj\Skautis;

use kissj\AbstractController;
use kissj\Application\CookieHandler;
use kissj\Event\EventRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SkautisController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly SkautisService $skautisService,
        private readonly CookieHandler $cookieHandler,
    ) {
    }

    /**
     * catch POST data from skautis, login user and redirect to dashboard
     */
    public function redirectFromSkautis(Request $request, Response $response): Response
    {
        // TODO fix into method $this->getParsedBody();
        /** @phpstan-ignore shipmonk.forbiddenCast */
        $this->skautisService->saveDataFromPost((array)$request->getParsedBody());

        $eventSlug = $this->getParameterFromQuery($request, 'ReturnUrl');
        $event = $this->eventRepository->getOneBy(['slug' => $eventSlug]);

        if (!$this->skautisService->isUserLoggedIn()) {
            $this->flashMessages->error('flash.error.skautisUserNotLoggedIn');

            return $this->redirect($request, $response, 'landing', ['eventSlug' => $eventSlug]);
        }

        $skautisUserData = $this->skautisService->getUserDetailsFromLoggedSkautisUser();
        if ($skautisUserData === null) {
            $this->flashMessages->error('flash.error.skautisUserError');

            return $this->redirect($request, $response, 'landing', ['eventSlug' => $eventSlug]);
        }

        $response = $this->cookieHandler->setCookie($response, 'lastLogin', 'skautis');

        $user = $this->skautisService->getOrCreateAndLogInSkautisUser($skautisUserData, $event);
        $this->skautisService->updateSkautisUserMembership($user, $skautisUserData);

        return $this->redirect($request, $response, 'getDashboard', ['eventSlug' => $eventSlug]);
    }
}
