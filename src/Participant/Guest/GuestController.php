<?php

namespace kissj\Participant\Guest;

use kissj\AbstractController;
use kissj\User\User;
use kissj\User\UserService;
use Slim\Http\Request;
use Slim\Http\Response;

class GuestController extends AbstractController {
    private $guestService;
    private $guestRepository;
    private $userService;

    public function __construct(
        GuestService $istService,
        GuestRepository $istRepository,
        UserService $userService
    ) {
        $this->guestService = $istService;
        $this->guestRepository = $istRepository;
        $this->userService = $userService;
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

        return $response->withRedirect($this->router->pathFor('guest-dashboard',
            ['eventSlug' => $guest->user->event->slug]));
    }

    public function showCloseRegistration(Request $request, Response $response) {
        $guest = $this->guestService->getGuest($request->getAttribute('user'));
        $validRegistration = $this->guestService->isCloseRegistrationValid($guest); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-guest.twig',
                ['dataProtectionUrl' => $guest->user->event->dataProtectionUrl]);
        }

        return $response->withRedirect($this->router->pathFor('guest-dashboard',
            ['eventSlug' => $guest->user->event->slug]
        ));
    }

    public function closeRegistration(Request $request, Response $response) {
        $guest = $this->guestService->getGuest($request->getAttribute('user'));
        $guest = $this->guestService->closeRegistration($guest);

        if ($guest->user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success('Registration successfully locked and send');
            $this->logger->info('Locked registration for Guest with ID '.$guest->id.', user ID '.$guest->user->id);
        } else {
            $this->flashMessages->error('Registration cannot be locked, data is not valid');
        }

        return $response->withRedirect($this->router->pathFor('guest-dashboard',
            ['eventSlug' => $guest->user->event->slug]));
    }

    // TODO
    public function approveIst(Request $request, Response $response, int $istId) {
        $ist = $this->guestService->getIstFromId($istId);
        $this->guestService->approveIst($ist);
        $payment = $this->paymentService->createNewPayment($ist->user->role);
        $this->guestService->sendPaymentByMail($payment, $ist);
        $this->flashMessages->success('Člen IST schválen, platba vygenerována a mail odeslán');
        $this->logger->info('Approved registration for IST with ID '.$ist->id);

        return $response->withRedirect($this->router->pathFor('admin-approving'));
    }

    public function showOpenIst(Request $request, Response $response) {
        $ist = $this->guestService->getIstFromId($args['istId']);

        return $this->view->render($response, 'admin/openIst.twig', ['ist' => $ist]);
    }

    public function openIst(Request $request, Response $response) {
        $ist = $this->guestService->getIstFromId($args['istId']);
        $this->guestService->openIst($ist);
        $reason = $request->getParsedBodyParam('reason');
        $this->guestService->sendDenialMail($ist, $reason);
        $this->flashMessages->info('Člen IST zamítnut, email o zamítnutí poslán');
        $this->logger->info('Denied registration for IST with ID '.$ist->id.' with reason: '.$reason);

        return $response->withRedirect($this->router->pathFor('admin-approving'));
    }
}
