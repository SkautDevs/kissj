<?php

declare(strict_types=1);

namespace kissj\Participant;

use Exception;
use kissj\AbstractController;
use kissj\Deal\Deal;
use kissj\Event\AbstractContentArbiter;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Deal\DealRepository;
use kissj\PdfGenerator\PdfGenerator;
use kissj\User\User;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;

class ParticipantController extends AbstractController
{
    public function __construct(
        private readonly ParticipantService $participantService,
        private readonly ParticipantRepository $participantRepository,
        private readonly PatrolParticipantRepository $patrolParticipantRepository,
        private readonly TroopParticipantRepository $troopParticipantRepository,
        private readonly DealRepository $dealRepository,
        private readonly PdfGenerator $pdfGenerator,
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
        $this->flashMessages->success('flash.success.detailsSaved');

        return $this->redirect($request, $response, 'getDashboard');
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
            $this->flashMessages->success('flash.success.locked');
            $this->logger->info('Locked registration for IST with ID ' . $participant->id
                . ', user ID ' . $participant->id);
        } else {
            $this->flashMessages->error('flash.error.wrongData');
        }

        return $this->redirect($request, $response, 'dashboard');
    }

    public function downloadReceipt(Request $request, Response $response, User $user): Response
    {
        if ($user->event->eventType->isReceiptAllowed() === false) {
            $this->flashMessages->error('flash.error.receiptNotAllowed');
            $this->sentryCollector->collect(new Exception('Receipt not allowed'));

            return $this->redirect($request, $response, 'dashboard');
        }

        $stream = fopen('php://temp', 'rb+');
        if ($stream === false) {
            $this->flashMessages->error('flash.error.cannotAccessTemp');
            $this->sentryCollector->collect(new Exception('Cannot access temp file'));

            return $this->redirect($request, $response, 'dashboard');
        }

        $participant = $this->participantRepository->getParticipantFromUser($user);

        fwrite($stream, $this->pdfGenerator->generatePdfReceipt(
            $participant,
            $user->event->eventType->getReceiptTemplateName($participant),
        ));
        rewind($stream);

        return $response->withHeader('Content-Type', 'application/pdf')->withBody(new Stream($stream));
    }

    /**
     * @return array<string, User|Participant|AbstractContentArbiter|PatrolParticipant[]|TroopParticipant[]|Deal[]>
     */
    private function getTemplateData(Participant $participant): array
    {
        $user = $participant->getUserButNotNull();
        $participants = [];
        if ($participant instanceof PatrolLeader) {
            $participants = $this->patrolParticipantRepository->findAllPatrolParticipantsForPatrolLeader($participant);
        } elseif ($participant instanceof TroopLeader) {
            $participants = $this->troopParticipantRepository->findAllTroopParticipantsForTroopLeader($participant);
        }

        return [
            'user' => $user,
            'person' => $participant,
            'participants' => $participants,
            'ca' => $this->participantService->getContentArbiterForParticipant($participant),
            'deals' => $this->dealRepository->obtainAllDealsForParticipant($participant),
        ];
    }
}
