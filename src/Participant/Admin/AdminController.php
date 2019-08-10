<?php

namespace kissj\Participant\Admin;

use Slim\Http\Request;
use Slim\Http\Response;

class AdminController {
    public function getDashboard(Request $request, Response $response, array $args) {
        $patrolStatistics = $this->get('patrolService')->getAllPatrolsStatistics();
        $istStatistics = $this->get('istService')->getAllIstsStatistics();

        return $this->view->render($response, 'admin/dashboard-admin.twig',
            ['eventName' => 'CEJ 2018', 'patrols' => $patrolStatistics, 'ists' => $istStatistics]);
    }

    public function showApproving(Request $request, Response $response, array $args) {
        $closedPatrols = $this->patrolService->getAllClosedPatrols();
        $closedIsts = $this->istService->getAllClosedIsts();

        return $this->view->render($response, 'admin/approving-admin.twig', [
            'eventName' => 'CEJ 2018',
            'closedPatrols' => $closedPatrols,
            'closedIsts' => $closedIsts,
        ]);
    }

    public function showPayments(Request $request, Response $response, array $args) {
        $approvedPatrols = $this->patrolService->getAllApprovedPatrolsWithPayment();
        $approvedIsts = $this->istService->getAllApprovedIstsWithPayment();

        $this->view->render($response, 'admin/payments-admin.twig', [
            'eventName' => 'CEJ 2018',
            'approvedPatrols' => $approvedPatrols,
            'approvedIsts' => $approvedIsts,
        ]);
    }

    public function setPaymentPaid(Request $request, Response $response, array $args) {
        /** @var \kissj\Payment\PaymentService $paymentService */
        $paymentService = $this->get('paymentService');
        $paymentId = $args['payment'];
        $paymentService->setPaymentPaid($paymentService->getPaymentFromId($paymentId));
        $this->flashMessages->success('Platba je označená jako zaplacená, mail o zaplacení odeslaný');
        $this->logger->info('Payment with ID '.$paymentId.' is set as paid by hand');

        return $response->withRedirect($this->router->pathFor('admin-payments'));
    }
}
