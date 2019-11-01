<?php

namespace kissj\Participant\Ist;

use kissj\AbstractController;
use kissj\Payment\PaymentService;
use kissj\User\User;
use Slim\Http\Request;
use Slim\Http\Response;

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

    public function showDashboard(Response $response, User $user) {
        $ist = $this->istService->getIst($user);
        $possibleOnePayment = $this->paymentService->findLastPayment($ist);

        return $this->view->render($response, 'dashboard-ist.twig',
            ['user' => $user, 'ist' => $ist, 'payment' => $possibleOnePayment]);
    }

    public function showDetailsChangeable(Request $request, Response $response) {
        $istDetails = $this->istService->getIst($request->getAttribute('user'));

        return $this->view->render($response, 'changeDetails-ist.twig',
            ['istDetails' => $istDetails]);
    }

    public function changeDetails(Request $request, Response $response) {
        $ist = $this->istService->addParamsIntoIst(
            $this->istService->getIst($request->getAttribute('user')),
            $request->getParams()
        );

        $this->istRepository->persist($ist);
        $this->flashMessages->success('Details successfully saved. ');

        return $response->withRedirect($this->router->pathFor('ist-dashboard',
            ['eventSlug' => $ist->user->event->slug]));
    }

    public function showCloseRegistration(Request $request, Response $response) {
        $ist = $this->istService->getIst($request->getAttribute('user'));
        $validRegistration = $this->istService->isCloseRegistrationValid($ist); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-ist.twig',
                ['dataProtectionUrl' => $ist->user->event->dataProtectionUrl]);
        }

        return $response->withRedirect($this->router->pathFor('ist-dashboard',
            ['eventSlug' => $ist->user->event->slug]
        ));
    }

    public function closeRegistration(Request $request, Response $response) {
        $ist = $this->istService->getIst($request->getAttribute('user'));
        $ist = $this->istService->closeRegistration($ist);

        if ($ist->user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success('Registration successfully locked and send');
            $this->logger->info('Locked registration for IST with ID '.$ist->id.', user ID '.$ist->user->id);
        } else {
            $this->flashMessages->error('Registration cannot be locked, data is not valid');
        }

        return $response->withRedirect($this->router->pathFor('ist-dashboard',
            ['eventSlug' => $ist->user->event->slug]));
    }

    // TODO fix
    public function approveIst(Request $request, Response $response, int $istId) {
        $ist = $this->istService->getIstFromId($istId);
        $this->istService->approveIst($ist);
        $payment = $this->paymentService->createNewPayment($ist->user->role);
        $this->istService->sendPaymentByMail($payment, $ist);
        $this->flashMessages->success('Člen IST schválen, platba vygenerována a mail odeslán');
        $this->logger->info('Approved registration for IST with ID '.$ist->id);

        return $response->withRedirect($this->router->pathFor('admin-approving'));
    }

    public function showOpenIst(Request $request, Response $response) {
        $ist = $this->istService->getIstFromId($args['istId']);

        return $this->view->render($response, 'admin/openIst.twig', ['ist' => $ist]);
    }

    public function openIst(Request $request, Response $response) {
        $ist = $this->istService->getIstFromId($args['istId']);
        $this->istService->openIst($ist);
        $reason = $request->getParsedBodyParam('reason');
        $this->istService->sendDenialMail($ist, $reason);
        $this->flashMessages->info('Člen IST zamítnut, email o zamítnutí poslán');
        $this->logger->info('Denied registration for IST with ID '.$ist->id.' with reason: '.$reason);

        return $response->withRedirect($this->router->pathFor('admin-approving'));
    }
}
