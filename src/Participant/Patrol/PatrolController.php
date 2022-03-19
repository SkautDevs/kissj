<?php declare(strict_types=1);

namespace kissj\Participant\Patrol;

use kissj\AbstractController;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PatrolController extends AbstractController
{
    public function __construct(
        private PatrolService $patrolService,
        private ParticipantService $participantService,
        private PatrolParticipantRepository $patrolParticipantRepository,
    ) {
    }

    public function showDashboard(Response $response, User $user): Response
    {
        $patrolLeader = $this->patrolService->getPatrolLeader($user);

        return $this->view->render(
            $response,
            'dashboard-pl.twig',
            [
                'user' => $user,
                'pl' => $patrolLeader,
                'person' => $patrolLeader,
                'participants' => $this->patrolParticipantRepository->findAllPatrolParticipantsForPatrolLeader($patrolLeader),
                'ca' => $user->event->eventType->getContentArbiterPatrolLeader(),
            ]
        );
    }

    public function showCloseRegistration(Request $request, Response $response, User $user): Response
    {
        $patrolLeader = $this->patrolService->getPatrolLeader($user);
        $validRegistration = $this->patrolService->isCloseRegistrationValid($patrolLeader); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-pl.twig',
                ['dataProtectionUrl' => $user->event->dataProtectionUrl]);
        }

        return $this->redirect($request, $response, 'pl-dashboard');
    }

    public function closeRegistration(Request $request, Response $response, User $user): Response
    {
        $patrolLeader = $this->patrolService->getPatrolLeader($user);
        $patrolLeader = $this->patrolService->closeRegistration($patrolLeader);

        $patrolLeaderUser = $patrolLeader->getUserButNotNull();
        if ($patrolLeaderUser->status === User::STATUS_CLOSED) {
            $this->flashMessages->success($this->translator->trans('flash.success.locked'));
            $this->logger->info('Locked registration for Patrol Leader with ID '
                . $patrolLeader->id . ', user ID ' . $patrolLeaderUser->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $this->redirect($request, $response, 'pl-dashboard');
    }

    public function addParticipant(Request $request, Response $response, User $user): Response
    {
        $patrolParticipant = $this->patrolService->addPatrolParticipant($this->patrolService->getPatrolLeader($user));

        return $this->redirect(
            $request,
            $response,
            'p-showChangeDetails',
            ['participantId' => (string)$patrolParticipant->id]
        );
    }

    public function showChangeDetailsPatrolParticipant(
        int $participantId,
        Response $response,
        ParticipantRepository $participantRepository,
        User $user,
    ): Response {
        /** @var PatrolParticipant $participant */
        $participant = $participantRepository->get($participantId);

        return $this->view->render(
            $response,
            'changeDetails-p.twig',
            [
                'pDetails' => $participant,
                'plDetails' => $participant->patrolLeader,
                'ca' => $user->event->eventType->getContentArbiterPatrolParticipant(),
            ]
        );
    }

    public function changeDetailsPatrolParticipant(
        int $participantId,
        Request $request,
        Response $response
    ): Response {
        /** @var array<string, string> $params */
        $params = $request->getParsedBody();
        /** @var PatrolParticipant $patrolParticipant */
        $patrolParticipant = $this->participantService->addParamsIntoParticipant(
            $this->patrolService->getPatrolParticipant($participantId),
            $params
        );
        $this->participantService->handleUploadedFiles($patrolParticipant, $request);

        $this->patrolParticipantRepository->persist($patrolParticipant);
        $this->flashMessages->success($this->translator->trans('flash.success.detailsSaved'));

        return $this->redirect(
            $request,
            $response,
            'pl-dashboard',
        );
    }

    public function showDeleteParticipant(int $participantId, Response $response): Response
    {
        $patrolParticipant = $this->patrolService->getPatrolParticipant($participantId);

        return $this->view->render($response, 'delete-p.twig', ['pDetail' => $patrolParticipant]);
    }

    public function deleteParticipant(int $participantId, Request $request, Response $response): Response
    {
        $this->patrolService->deletePatrolParticipant($this->patrolService->getPatrolParticipant($participantId));
        $this->flashMessages->info($this->translator->trans('flash.info.participantDeleted'));

        return $this->redirect(
            $request,
            $response,
            'pl-dashboard',
        );
    }

    public function showParticipant(int $participantId, Response $response, User $user): Response
    {
        $patrolParticipant = $this->patrolService->getPatrolParticipant($participantId);

        return $this->view->render(
            $response,
            'show-p.twig',
            [
                'pDetail' => $patrolParticipant,
                'ca' => $user->event->eventType->getContentArbiterPatrolParticipant(),
            ]
        );
    }
}
