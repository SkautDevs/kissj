<?php

namespace kissj\Participant\Guest;

use kissj\AbstractController;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GuestController extends AbstractController
{
    public function __construct(
        private GuestService $guestService,
        private GuestRepository $guestRepository,
    ) {
    }

    public function showDashboard(Response $response, User $user): Response
    {
        $guest = $this->guestService->getGuest($user);

        return $this->view->render($response, 'dashboard-guest.twig',
            ['user' => $user, 'guest' => $guest]);
    }

    public function showDetailsChangeable(Request $request, Response $response): Response
    {
        $guestDetails = $this->guestService->getGuest($request->getAttribute('user'));

        return $this->view->render($response, 'changeDetails-guest.twig',
            ['guestDetails' => $guestDetails]);
    }

    public function changeDetails(Request $request, Response $response): Response
    {
        $guest = $this->guestService->addParamsIntoGuest(
            $this->guestService->getGuest($request->getAttribute('user')),
            $request->getParsedBody()
        );

        $this->guestRepository->persist($guest);
        $this->flashMessages->success('Details successfully saved. ');

        return $this->redirect($request, $response, 'guest-dashboard', ['eventSlug' => $guest->user->event->slug]);
    }

    public function showCloseRegistration(Request $request, Response $response): Response
    {
        $guest = $this->guestService->getGuest($request->getAttribute('user'));
        $validRegistration = $this->guestService->isCloseRegistrationValid($guest); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-guest.twig',
                ['dataProtectionUrl' => $guest->user->event->dataProtectionUrl]);
        }

        return $this->redirect($request, $response, 'guest-dashboard', ['eventSlug' => $guest->user->event->slug]);
    }

    public function closeRegistration(Request $request, Response $response): Response
    {
        $guest = $this->guestService->getGuest($request->getAttribute('user'));
        $guest = $this->guestService->closeRegistration($guest);

        if ($guest->user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success('Registration successfully locked and sent');
            $this->logger->info('Locked registration for Guest with ID '.$guest->id
                .', user ID '.$guest->user->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $this->redirect($request, $response, 'guest-dashboard', ['eventSlug' => $guest->user->event->slug]);
    }

    public function showOpenGuest(int $guestId, Response $response): Response
    {
        $guest = $this->guestRepository->get($guestId);

        return $this->view->render($response, 'admin/openGuest-admin.twig', ['guest' => $guest]);
    }

    public function openGuest(int $guestId, Request $request, Response $response)
    {
        $reason = htmlspecialchars($request->getParsedBody()['reason'], ENT_QUOTES);
        /** @var Guest $guest */
        $guest = $this->guestRepository->get($guestId);
        $this->guestService->openRegistration($guest, $reason);
        $this->flashMessages->info('guest participant denied, email successfully sent');
        $this->logger->info('Denied registration for guest with ID '.$guest->id.' with reason: '.$reason);

        return $this->redirect($request, $response, 'admin-show-approving', ['eventSlug' => $guest->user->event->slug]);
    }

    public function approveGuest(int $guestId, Request $request, Response $response): Response
    {
        /** @var Guest $guest */
        $guest = $this->guestRepository->get($guestId);
        $this->guestService->finishRegistration($guest);
        $this->flashMessages->success($this->translator->trans('flash.success.guestApproved'));
        $this->logger->info('Approved (no payment was sent) registration for guest with ID '.$guest->id);

        return $this->redirect($request, $response, 'admin-show-approving', ['eventSlug' => $guest->user->event->slug]);
    }
}
