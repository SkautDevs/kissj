<?php

namespace kissj\Participant\Guest;

use kissj\AbstractController;
use kissj\User\User;
use Slim\Http\Request;
use Slim\Http\Response;

class GuestController extends AbstractController {
    private $guestService;
    private $guestRepository;

    public function __construct(
        GuestService $istService,
        GuestRepository $istRepository
    ) {
        $this->guestService = $istService;
        $this->guestRepository = $istRepository;
    }

    public function showDashboard(Response $response, User $user) {
        $guest = $this->guestService->getGuest($user);

        return $this->view->render($response, 'dashboard-guest.twig',
            ['user' => $user, 'guest' => $guest]);
    }

    public function showDetailsChangeable(Request $request, Response $response) {
        $guestDetails = $this->guestService->getGuest($request->getAttribute('user'));

        return $this->view->render($response, 'changeDetails-guest.twig',
            ['guestDetails' => $guestDetails]);
    }

    public function changeDetails(Request $request, Response $response) {
        $guest = $this->guestService->addParamsIntoGuest(
            $this->guestService->getGuest($request->getAttribute('user')),
            $request->getParams()
        );

        $this->guestRepository->persist($guest);
        $this->flashMessages->success('Details successfully saved. ');

        return $response->withRedirect($this->router->urlFor('guest-dashboard',
            ['eventSlug' => $guest->user->event->slug]));
    }

    public function showCloseRegistration(Request $request, Response $response) {
        $guest = $this->guestService->getGuest($request->getAttribute('user'));
        $validRegistration = $this->guestService->isCloseRegistrationValid($guest); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-guest.twig',
                ['dataProtectionUrl' => $guest->user->event->dataProtectionUrl]);
        }

        return $response->withRedirect($this->router->urlFor('guest-dashboard',
            ['eventSlug' => $guest->user->event->slug]
        ));
    }

    public function closeRegistration(Request $request, Response $response) {
        $guest = $this->guestService->getGuest($request->getAttribute('user'));
        $guest = $this->guestService->closeRegistration($guest);

        if ($guest->user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success('Registration successfully locked and sent');
            $this->logger->info('Locked registration for Guest with ID '.$guest->id.', user ID '.$guest->user->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $response->withRedirect($this->router->urlFor('guest-dashboard',
            ['eventSlug' => $guest->user->event->slug]));
    }

    public function showOpenGuest(int $guestId, Response $response) {
        $guest = $this->guestRepository->find($guestId);

        return $this->view->render($response, 'admin/openGuest-admin.twig', ['guest' => $guest]);
    }

    public function openGuest(int $guestId, Request $request, Response $response) {
        $reason = htmlspecialchars($request->getParam('reason'), ENT_QUOTES);
        /** @var Guest $guest */
        $guest = $this->guestRepository->find($guestId);
        $this->guestService->openRegistration($guest, $reason);
        $this->flashMessages->info('guest participant denied, email successfully sent');
        $this->logger->info('Denied registration for guest with ID '.$guest->id.' with reason: '.$reason);

        return $response->withRedirect(
            $this->router->urlFor('admin-show-approving', ['eventSlug' => $guest->user->event->slug])
        );
    }

    public function approveGuest(int $guestId, Response $response) {
        /** @var Guest $guest */
        $guest = $this->guestRepository->find($guestId);
        $this->guestService->finishRegistration($guest);
        $this->flashMessages->success('guest participant is approved, mail was sent (withnout payment)');
        $this->logger->info('Approved (no payment was sent) registration for guest with ID '.$guest->id);

        return $response->withRedirect($this->router->urlFor(
            'admin-show-approving', ['eventSlug' => $guest->user->event->slug])
        );
    }
}
