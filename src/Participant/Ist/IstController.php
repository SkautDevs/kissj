<?php

namespace kissj\Participant\Ist;

use kissj\AbstractController;
use kissj\Payment\PaymentService;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IstController extends AbstractController {
    private $istService;
    private $istRepository;
    private $paymentService;

    public function __construct(
        IstService $istService,
        IstRepository $istRepository,
        PaymentService $paymentService
    ) {
        $this->istService = $istService;
        $this->istRepository = $istRepository;
        $this->paymentService = $paymentService;
    }

    public function showDashboard(Response $response, User $user): Response {
        $ist = $this->istService->getIst($user);
        $possibleOnePayment = $this->paymentService->findLastPayment($ist);

        return $this->view->render($response, 'dashboard-ist.twig',
            ['user' => $user, 'ist' => $ist, 'payment' => $possibleOnePayment]);
    }

    public function showDetailsChangeable(Request $request, Response $response): Response {
        $istDetails = $this->istService->getIst($request->getAttribute('user'));

        return $this->view->render($response, 'changeDetails-ist.twig',
            ['istDetails' => $istDetails]);
    }

    public function changeDetails(Request $request, Response $response): Response {
        $ist = $this->istService->addParamsIntoIst(
            $this->istService->getIst($request->getAttribute('user')),
            $request->getParsedBody()
        );

        $this->istRepository->persist($ist);
        $this->flashMessages->success('Details successfully saved. ');

        return $this->redirect($request, $response, 'ist-dashboard', ['eventSlug' => $ist->user->event->slug]);
    }

    public function showCloseRegistration(Request $request, Response $response): Response {
        $ist = $this->istService->getIst($request->getAttribute('user')); // TODO change to autowiring
        $validRegistration = $this->istService->isCloseRegistrationValid($ist); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-ist.twig',
                ['dataProtectionUrl' => $ist->user->event->dataProtectionUrl]);
        }

        return $this->redirect($request, $response, 'ist-dashboard', ['eventSlug' => $ist->user->event->slug]);
    }

    public function closeRegistration(Request $request, Response $response): Response {
        $ist = $this->istService->getIst($request->getAttribute('user'));
        $ist = $this->istService->closeRegistration($ist);

        if ($ist->user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success('Registration successfully locked and sent');
            $this->logger->info('Locked registration for IST with ID '.$ist->id.', user ID '.$ist->user->id);
        } else {
            $this->flashMessages->error('Registration cannot be locked, data is not valid');
        }

        return $this->redirect($request, $response, 'ist-dashboard', ['eventSlug' => $ist->user->event->slug]);
    }

    public function showOpenIst(int $istId, Response $response): Response {
        $ist = $this->istRepository->find($istId);

        return $this->view->render($response, 'admin/openIst-admin.twig', ['ist' => $ist]);
    }

    public function openIst(int $istId, Request $request, Response $response): Response {
        $reason = htmlspecialchars($request->getParsedBody()['reason'], ENT_QUOTES);
        /** @var Ist $ist */
        $ist = $this->istRepository->find($istId);
        $this->istService->openRegistration($ist, $reason);
        $this->flashMessages->info('IST participant denied, email successfully sent');
        $this->logger->info('Denied registration for IST with ID '.$ist->id.' with reason: '.$reason);

        return $this->redirect($request, $response, 'admin-show-approving', ['eventSlug' => $ist->user->event->slug]);
    }

    public function approveIst(int $istId, Request $request, Response $response): Response {
        /** @var Ist $ist */
        $ist = $this->istRepository->find($istId);
        $this->istService->approveRegistration($ist);
        $this->flashMessages->success('IST participant is approved, payment is generated and mail sent');
        $this->logger->info('Approved registration for IST with ID '.$ist->id);

        return $this->redirect($request, $response, 'admin-show-approving', ['eventSlug' => $ist->user->event->slug]);
    }
}
