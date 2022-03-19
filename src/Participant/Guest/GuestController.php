<?php declare(strict_types=1);

namespace kissj\Participant\Guest;

use kissj\AbstractController;
use kissj\Participant\ParticipantService;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GuestController extends AbstractController
{
    public function __construct(
        private GuestService $guestService,
        private ParticipantService $participantService,
    ) {
    }

    public function showDashboard(Response $response, User $user): Response
    {
        $guest = $this->guestService->getGuest($user);

        return $this->view->render($response, 'dashboard-guest.twig', [
            'user' => $user,
            'guest' => $guest,
            'person' => $guest,
            'ca' => $user->event->eventType->getContentArbiterGuest(),
        ]);
    }

    public function showCloseRegistration(Request $request, Response $response, User $user): Response
    {
        $guest = $this->guestService->getGuest($user);
        $validRegistration = $this->participantService->isCloseRegistrationValid($guest); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-guest.twig',
                ['dataProtectionUrl' => $user->event->dataProtectionUrl]);
        }

        return $this->redirect($request, $response, 'guest-dashboard');
    }

    public function closeRegistration(Request $request, Response $response, User $user): Response
    {
        $guest = $this->guestService->getGuest($user);
        $guest = $this->participantService->closeRegistration($guest);

        if ($user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success('Registration successfully locked and sent');
            $this->logger->info('Locked registration for Guest with ID ' . $guest->id
                . ', user ID ' . $user->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $this->redirect($request, $response, 'guest-dashboard');
    }
}
