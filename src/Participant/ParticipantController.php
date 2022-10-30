<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\AbstractController;
use kissj\Event\AbstractContentArbiter;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\User;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ParticipantController extends AbstractController
{
    public function __construct(
        private ParticipantService $participantService,
        private ParticipantRepository $participantRepository,
        private PatrolParticipantRepository $patrolParticipantRepository,
    ) {
    }

    public function showDashboard(Response $response, User $user): Response
    {
        return $this->view->render(
            $response,
            'participant/dashboard.twig',
            $this->getTemplateData($this->participantRepository->getParticipantFromUser($user)),
        );
    }

    public function showDetailsChangeable(Response $response, User $user): Response
    {
        return $this->view->render(
            $response,
            'participant/changeDetails.twig',
            $this->getTemplateData($this->participantRepository->getParticipantFromUser($user)),
        );
    }

    public function changeDetails(Request $request, Response $response, User $user): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);

        /** @var string[] $parsed */
        $parsed = $request->getParsedBody();
        $this->participantService->addParamsIntoParticipant($participant, $parsed);
        $this->participantService->handleUploadedFiles($participant, $request);

        $this->participantRepository->persist($participant);
        $this->flashMessages->success($this->translator->trans('flash.success.detailsSaved'));

        return $this->redirect($request, $response, 'getDashboard'); // TODO change to common dashboard when possible
    }

    public function showCloseRegistration(Request $request, Response $response, User $user): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);

        if ($this->participantService->isCloseRegistrationValid($participant)) {
            return $this->view->render(
                $response,
                'participant/closeRegistration.twig',
                ['dataProtectionUrl' => $user->event->dataProtectionUrl]
            );
        }

        return $this->redirect($request, $response, 'dashboard');
    }

    public function closeRegistration(Request $request, Response $response, User $user): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);
        $participant = $this->participantService->closeRegistration($participant);

        if ($participant->getUserButNotNull()->status === UserStatus::Closed) {
            $this->flashMessages->success($this->translator->trans('flash.success.locked'));
            $this->logger->info('Locked registration for IST with ID ' . $participant->id
                . ', user ID ' . $participant->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $this->redirect($request, $response, 'dashboard');
    }

    /**
     * @return array<string, User|Participant|AbstractContentArbiter|PatrolParticipant[]>
     */
    private function getTemplateData(Participant $participant): array
    {
        $user = $participant->getUserButNotNull();
        $participants = [];
        if ($participant instanceof PatrolLeader) {
            $participants = $this->patrolParticipantRepository->findAllPatrolParticipantsForPatrolLeader($participant);
        }
    
        return [
            'user' => $user,
            'person' => $participant,
            'participants' => $participants,
            'ca' => $this->participantService->getContentArbiterForParticipant($participant),
        ];
    }
}
