<?php

namespace kissj\Participant\Patrol;

use kissj\AbstractController;
use kissj\Event\ContentArbiterPatrolLeader;
use kissj\Event\ContentArbiterPatrolParticipant;
use kissj\Participant\ParticipantRepository;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PatrolController extends AbstractController {
    private PatrolService $patrolService;
    private PatrolLeaderRepository $patrolLeaderRepository;
    private PatrolParticipantRepository $patrolParticipantRepository;
    private ContentArbiterPatrolLeader $contentArbiterPl;
    private ContentArbiterPatrolParticipant $contentArbiterPp;

    public function __construct(
        PatrolService $patrolService,
        PatrolLeaderRepository $patrolLeaderRepository,
        PatrolParticipantRepository $patrolParticipantRepository,
        ContentArbiterPatrolLeader $contentArbiterPatrolLeader,
        ContentArbiterPatrolParticipant $contentArbiterPatrolParticipant
    ) {
        $this->patrolService = $patrolService;
        $this->patrolLeaderRepository = $patrolLeaderRepository;
        $this->patrolParticipantRepository = $patrolParticipantRepository;
        $this->contentArbiterPl = $contentArbiterPatrolLeader;
        $this->contentArbiterPp = $contentArbiterPatrolParticipant;
    }

    public function showDashboard(Response $response, User $user) {
        $patrolLeader = $this->patrolService->getPatrolLeader($user);

        return $this->view->render(
            $response,
            'dashboard-pl.twig',
            [
                'user' => $user,
                'pl' => $patrolLeader,
                'participants' => $this->patrolParticipantRepository->findBy(['patrol_leader_id' => $patrolLeader->id]),
                'ca' => $this->contentArbiterPl,
            ]
        );
    }

    public function showDetailsChangeableLeader(Request $request, Response $response) {
        $patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));

        return $this->view->render($response, 'changeDetails-pl.twig',
            ['plDetails' => $patrolLeader, 'ca' => $this->contentArbiterPl]);
    }

    public function changeDetailsLeader(Request $request, Response $response) {
        $pl = $this->patrolService->getPatrolLeader($request->getAttribute('user'));

        if ($this->contentArbiterPl->uploadFile) {
            $uploadedFile = $this->resolveUploadedFiles($request->getUploadedFiles());
            if ($uploadedFile === null) {
                return $this->redirect($request, $response, 'pl-dashboard', ['eventSlug' => $pl->user->event->slug]);
            }

            $this->patrolService->saveFileTo($pl, $uploadedFile);
        }

        $pl = $this->patrolService->addParamsIntoPatrolLeader($pl, $request->getParsedBody());

        $this->patrolLeaderRepository->persist($pl);
        $this->flashMessages->success($this->translator->trans('flash.success.detailsSaved'));

        return $this->redirect($request, $response, 'pl-dashboard', ['eventSlug' => $pl->user->event->slug]);
    }

    public function showCloseRegistration(Request $request, Response $response) {
        $patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
        $validRegistration = $this->patrolService->isCloseRegistrationValid($patrolLeader); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-pl.twig',
                ['dataProtectionUrl' => $patrolLeader->user->event->dataProtectionUrl]);
        }

        return $this->redirect($request, $response, 'pl-dashboard', ['eventSlug' => $patrolLeader->user->event->slug]);
    }

    public function closeRegistration(Request $request, Response $response) {
        $patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
        $patrolLeader = $this->patrolService->closeRegistration($patrolLeader);

        if ($patrolLeader->user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success($this->translator->trans('flash.success.locked'));
            $this->logger->info('Locked registration for Patrol Leader with ID '
                .$patrolLeader->id.', user ID '.$patrolLeader->user->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $this->redirect($request, $response, 'pl-dashboard', ['eventSlug' => $patrolLeader->user->event->slug]);
    }

    public function addParticipant(Request $request, Response $response) {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $patrolParticipant = $this->patrolService->addPatrolParticipant($this->patrolService->getPatrolLeader($user));

        return $this->redirect(
            $request,
            $response,
            'p-showChangeDetails',
            ['eventSlug' => $user->event->slug, 'participantId' => $patrolParticipant->id]
        );
    }

    public function showChangeDetailsPatrolParticipant(
        int $participantId,
        Response $response,
        ParticipantRepository $participantRepository
    ) {
        /** @var PatrolParticipant $participant */
        $participant = $participantRepository->find($participantId);

        return $this->view->render(
            $response,
            'changeDetails-p.twig',
            ['pDetails' => $participant, 'plDetails' => $participant->patrolLeader, 'ca' => $this->contentArbiterPp]
        );
    }

    public function changeDetailsPatrolParticipant(int $participantId, Request $request, Response $response) {
        $patrolParticipant = $this->patrolService->addParamsIntoPatrolParticipant(
            $this->patrolService->getPatrolParticipant($participantId),
            $request->getParsedBody()
        );

        $this->patrolLeaderRepository->persist($patrolParticipant);
        $this->flashMessages->success($this->translator->trans('flash.success.detailsSaved'));

        return $this->redirect(
            $request,
            $response,
            'pl-dashboard',
            ['eventSlug' => $patrolParticipant->patrolLeader->user->event->slug]
        );
    }

    public function showDeleteParticipant(int $participantId, Response $response) {
        $patrolParticipant = $this->patrolService->getPatrolParticipant($participantId);

        return $this->view->render($response, 'delete-p.twig', ['pDetail' => $patrolParticipant]);
    }

    public function deleteParticipant(int $participantId, Request $request, Response $response) {
        $this->patrolService->deletePatrolParticipant($this->patrolService->getPatrolParticipant($participantId));
        $this->flashMessages->info($this->translator->trans('participantDeleted'));

        return $this->redirect(
            $request,
            $response,
            'pl-dashboard',
            ['eventSlug' => $request->getAttribute('user')->event->slug]
        );
    }

    public function showParticipant(int $participantId, Response $response) {
        $patrolParticipant = $this->patrolService->getPatrolParticipant($participantId);

        return $this->view->render(
            $response,
            'show-p.twig',
            ['pDetail' => $patrolParticipant, 'ca' => $this->contentArbiterPp]
        );
    }

    public function showOpenPatrol(int $patrolLeaderId, Response $response) {
        $patrolLeader = $this->patrolLeaderRepository->find($patrolLeaderId);

        return $this->view->render($response, 'admin/openPatrol-admin.twig', ['patrolLeader' => $patrolLeader]);
    }

    public function openPatrol(int $patrolLeaderId, Request $request, Response $response) {
        $reason = htmlspecialchars($request->getParsedBody()['reason'], ENT_QUOTES);
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $this->patrolLeaderRepository->find($patrolLeaderId);
        $this->patrolService->openRegistration($patrolLeader, $reason);
        $this->flashMessages->info($this->translator->trans('flash.info.patrolDenied'));
        $this->logger->info(
            'Denied registration for Patrol with Patrol Leader ID '.$patrolLeader->id.' with reason: '.$reason
        );

        return $this->redirect(
            $request,
            $response,
            'admin-show-approving',
            ['eventSlug' => $patrolLeader->user->event->slug]
        );
    }

    public function approvePatrol(int $patrolLeaderId, Request $request, Response $response) {
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $this->patrolLeaderRepository->find($patrolLeaderId);
        $this->patrolService->approveRegistration($patrolLeader);
        $this->flashMessages->success($this->translator->trans('flash.success.patrolApproved'));
        $this->logger->info('Approved registration for Patrol with Patrol Leader ID '.$patrolLeader->id);

        return $this->redirect($request,
            $response,
            'admin-show-approving',
            ['eventSlug' => $patrolLeader->user->event->slug]
        );
    }
}
