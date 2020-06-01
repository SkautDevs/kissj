<?php

namespace kissj\Participant\Patrol;

use kissj\AbstractController;
use kissj\Participant\ParticipantRepository;
use kissj\Payment\PaymentService;
use kissj\User\User;
use Slim\Http\Request;
use Slim\Http\Response;

class PatrolController extends AbstractController {
    private $patrolService;
    private $patrolLeaderRepository;
    private $patrolParticipantRepository;
    private $paymentService;

    public function __construct(
        PatrolService $patrolService,
        PatrolLeaderRepository $patrolLeaderRepository,
        PatrolParticipantRepository $patrolParticipantRepository,
        PaymentService $paymentService
    ) {
        $this->patrolService = $patrolService;
        $this->patrolLeaderRepository = $patrolLeaderRepository;
        $this->patrolParticipantRepository = $patrolParticipantRepository;
        $this->paymentService = $paymentService;
    }

    public function showDashboard(Response $response, User $user) {
        $patrolLeader = $this->patrolService->getPatrolLeader($user);
        $possibleOnePayment = $this->paymentService->findLastPayment($patrolLeader);
        $allLeadersParticipants = $this->patrolParticipantRepository->findBy(['patrol_leader_id' => $patrolLeader->id]);

        return $this->view->render($response, 'dashboard-pl.twig',
            [
                'user' => $user,
                'pl' => $patrolLeader,
                'payment' => $possibleOnePayment,
                'particiants' => $allLeadersParticipants,
            ]);
    }

    public function showDetailsChangeableLeader(Request $request, Response $response) {
        $patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));

        return $this->view->render($response, 'changeDetails-pl.twig',
            ['plDetails' => $patrolLeader]);
    }

    public function changeDetailsLeader(Request $request, Response $response) {
        $patrolLeader = $this->patrolService->addParamsIntoPatrolLeader(
            $this->patrolService->getPatrolLeader($request->getAttribute('user')),
            $request->getParams()
        );

        $this->patrolLeaderRepository->persist($patrolLeader);
        $this->flashMessages->success('Details successfully saved.');

        return $response->withRedirect($this->router->urlFor('pl-dashboard',
            ['eventSlug' => $patrolLeader->user->event->slug]));
    }

    public function showCloseRegistration(Request $request, Response $response) {
        $patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
        $validRegistration = $this->patrolService->isCloseRegistrationValid($patrolLeader); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-pl.twig',
                ['dataProtectionUrl' => $patrolLeader->user->event->dataProtectionUrl]);
        }

        return $response->withRedirect($this->router->urlFor('pl-dashboard',
            ['eventSlug' => $patrolLeader->user->event->slug]
        ));
    }

    public function closeRegistration(Request $request, Response $response) {
        $patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
        $patrolLeader = $this->patrolService->closeRegistration($patrolLeader);

        if ($patrolLeader->user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success('Registration successfully locked and sent');
            $this->logger->info('Locked registration for Patrol Leader with ID '
                .$patrolLeader->id.', user ID '.$patrolLeader->user->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $response->withRedirect($this->router->urlFor('pl-dashboard',
            ['eventSlug' => $patrolLeader->user->event->slug]));
    }

    public function addParticipant(Request $request, Response $response) {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $patrolParticipant = $this->patrolService->addPatrolParticipant($this->patrolService->getPatrolLeader($user));

        return $response->withRedirect($this->router->urlFor(
            'p-showChangeDetails', ['eventSlug' => $user->event->slug, 'participantId' => $patrolParticipant->id])
        );
    }

    public function showChangeDetailsPatrolParticipant(
        int $participantId,
        Response $response,
        ParticipantRepository $participantRepository
    ) {
        /** @var PatrolParticipant $participant */
        $participant = $participantRepository->find($participantId);

        return $this->view->render($response, 'changeDetails-p.twig',
            ['pDetails' => $participant, 'plDetails' => $participant->patrolLeader]);
    }

    public function changeDetailsPatrolParticipant(int $participantId, Request $request, Response $response) {
        $patrolParticipant = $this->patrolService->addParamsIntoPatrolParticipant(
            $this->patrolService->getPatrolParticipant($participantId),
            $request->getParams()
        );

        $this->patrolLeaderRepository->persist($patrolParticipant);
        $this->flashMessages->success('Details successfully saved.');

        return $response->withRedirect($this->router->urlFor('pl-dashboard',
            ['eventSlug' => $patrolParticipant->patrolLeader->user->event->slug]));
    }

    public function showDeleteParticipant(int $participantId, Response $response) {
        $patrolParticipant = $this->patrolService->getPatrolParticipant($participantId);

        return $this->view->render($response, 'delete-p.twig', ['pDetail' => $patrolParticipant]);
    }

    public function deleteParticipant(int $participantId, Request $request, Response $response) {
        $this->patrolService->deletePatrolParticipant($this->patrolService->getPatrolParticipant($participantId));
        $this->flashMessages->info('Participant was deleted');

        return $response->withRedirect($this->router->urlFor('pl-dashboard',
            ['eventSlug' => $request->getAttribute('user')->event->slug]));
    }

    public function showParticipant(int $participantId, Response $response) {
        $patrolParticipant = $this->patrolService->getPatrolParticipant($participantId);

        return $this->view->render($response, 'show-p.twig', ['pDetail' => $patrolParticipant]);
    }

    public function showOpenPatrol(int $patrolLeaderId, Response $response) {
        $patrolLeader = $this->patrolLeaderRepository->find($patrolLeaderId);

        return $this->view->render($response, 'admin/openPatrol-admin.twig', ['patrolLeader' => $patrolLeader]);
    }

    public function openPatrol(int $patrolLeaderId, Request $request, Response $response) {
        $reason = htmlspecialchars($request->getParam('reason'), ENT_QUOTES);
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $this->patrolLeaderRepository->find($patrolLeaderId);
        $this->patrolService->openRegistration($patrolLeader, $reason);
        $this->flashMessages->info('Patrol denied, email successfully sent');
        $this->logger->info(
            'Denied registration for Patrol with Patrol Leader ID '.$patrolLeader->id.' with reason: '.$reason
        );

        return $response->withRedirect(
            $this->router->urlFor('admin-show-approving', ['eventSlug' => $patrolLeader->user->event->slug])
        );
    }

    public function approvePatrol(int $patrolLeaderId, Response $response) {
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $this->patrolLeaderRepository->find($patrolLeaderId);
        $this->patrolService->approveRegistration($patrolLeader);
        $this->flashMessages->success('Patrol is approved, payment is generated and mail sent');
        $this->logger->info('Approved registration for Patrol with Patrol Leader ID '.$patrolLeader->id);

        return $response->withRedirect($this->router->urlFor(
            'admin-show-approving', ['eventSlug' => $patrolLeader->user->event->slug])
        );
    }
}
