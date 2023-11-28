<?php

declare(strict_types=1);

namespace kissj\Skautis;

use kissj\AbstractController;
use kissj\Event\EventRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class SkautisController extends AbstractController
{
    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly SkautisService $skautisService,
    ) {
    }

    /**
     * catch POST data from skautis, login user and redirect to dashboard
     */
    public function redirectFromSkautis(Request $request, Response $response): Response
    {
        $this->skautisService->saveDataFromPost((array)$request->getParsedBody());

        $eventSlug = $this->getParameterFromQuery($request, 'ReturnUrl');
        $event = $this->eventRepository->getOneBy(['slug' => $eventSlug]);

        if (!$this->skautisService->isUserLoggedIn()) {
            $this->flashMessages->error($this->translator->trans('flash.error.skautisUserNotLoggedIn'));
            
            return $this->redirect($request, $response, 'landing', ['eventSlug' => $eventSlug]);
        }

        $skautisUserData = $this->skautisService->getUserDetailsFromLoggedSkautisUser();
        if ($skautisUserData === null) {
            $this->flashMessages->error($this->translator->trans('flash.error.skautisUserError'));

            return $this->redirect($request, $response, 'landing', ['eventSlug' => $eventSlug]);
        }

        $user = $this->skautisService->getOrCreateAndLogInSkautisUser($skautisUserData, $event);
        $this->skautisService->updateSkautisUserMembership($user, $skautisUserData);

        return $this->redirect($request, $response, 'getDashboard', ['eventSlug' => $eventSlug]);
    }
}
