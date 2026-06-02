<?php

declare(strict_types=1);

namespace kissj\Participant;

use Exception;
use kissj\AbstractController;
use kissj\Deal\Deal;
use kissj\Event\AbstractContentArbiter;
use kissj\Event\ContentArbiter\ContentArbiterItem;
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
        private readonly ParticipantFileService $participantFileService,
        private readonly ParticipantRepository $participantRepository,
        private readonly PatrolParticipantRepository $patrolParticipantRepository,
        private readonly TroopParticipantRepository $troopParticipantRepository,
        private readonly DealRepository $dealRepository,
        private readonly PdfGenerator $pdfGenerator,
    ) {
    }

    public function showDashboard(Request $request, Response $response, User $user): Response
    {
        $templateData = $this->getTemplateData($this->participantRepository->getParticipantFromUser($user));
        $templateData['celebrate'] = ($request->getQueryParams()['celebrate'] ?? null) === '1';

        return $this->view->render(
            $response,
            'participant/dashboard.twig',
            $templateData,
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

    public function showDetailsChangeableAfterLock(Response $response, User $user): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);
        $ca = $user->event->eventType->getContentArbiterForRole($participant->getRoleOrFail());

        return $this->view->render(
            $response,
            'participant/changeDetailsAfterLock.twig',
            [
                'user' => $user,
                'person' => $participant,
                'ca' => $ca,
            ],
        );
    }

    public function changeDetailsAfterLock(Request $request, Response $response, User $user): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);
        $ca = $user->event->eventType->getContentArbiterForRole($participant->getRoleOrFail());
        $editableItems = $ca->getEditableAfterLockItems();

        /** @var array<string, string|null> $parsed */
        $parsed = $request->getParsedBody();
        $this->participantService->updateEditableAfterLockFields($participant, $parsed, $editableItems);
        $this->participantFileService->handleUploadedFiles($participant, $request, $editableItems);

        $editedSlugs = array_map(fn (ContentArbiterItem $item) => $item->slug, $editableItems);
        $this->logger->info('Changed details after lock for participant ID ' . $participant->id
            . ', user ID ' . $user->id . ', fields: ' . implode(', ', $editedSlugs));

        $this->flashMessages->success('flash.success.detailsSaved');

        return $this->redirect($request, $response, 'getDashboard');
    }

    public function changeDetails(Request $request, Response $response, User $user): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);

        /** @var array<string, string|null> $parsed */
        $parsed = $request->getParsedBody();
        $ca = $user->event->eventType->getContentArbiterForRole($participant->getRoleOrFail());
        $this->participantService->addParamsIntoParticipant($participant, $parsed);
        $this->participantFileService->handleUploadedFiles($participant, $request, $ca->getAllowedItems());

        $this->participantRepository->persist($participant);
        $this->flashMessages->success('flash.success.detailsSaved');

        return $this->redirect($request, $response, 'getDashboard');
    }

    public function showCloseRegistration(Request $request, Response $response, User $user): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);

        $closeResult = $this->participantService->isCloseRegistrationValid($participant);
        $this->flashRegistrationCloseResult($closeResult);

        if ($closeResult->isValid) {
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

            return $this->redirect($request, $response, 'dashboard', queryParams: ['celebrate' => '1']);
        }

        $this->flashMessages->error('flash.error.wrongData');

        return $this->redirect($request, $response, 'dashboard');
    }

    public function downloadReceipt(Request $request, Response $response, User $user): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);

        if ($user->event->eventType->showReceiptToParticipant($participant) === false) {
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

        fwrite($stream, $this->pdfGenerator->generatePdfReceipt(
            $participant,
            $user->event->eventType->getReceiptTemplateName($participant),
        ));
        rewind($stream);

        return $response->withHeader('Content-Type', 'application/pdf')->withBody(new Stream($stream));
    }

    public function downloadFile(Request $request, Response $response, string $filename): Response
    {
        if (preg_match('/^[a-f0-9]{32}$/', $filename) !== 1) {
            // prevent possible access to filesystem with filtering the characters
            return $response->withStatus(400);
        }

        $file = $this->fileHandler->getFile($filename);

        /** @var Participant $participant */
        $participant = $request->getAttribute('participant');
        $originalFilename = $participant->getOriginalFilenameForStoredFile($filename) ?? $filename;

        return $response
            ->withHeader('Content-Type', $file->mimeContentType)
            ->withHeader('Content-Disposition', 'inline; filename="' . $originalFilename . '"')
            ->withBody($file->stream);
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
            'ca' => $user->event->eventType->getContentArbiterForRole($participant->getRoleOrFail()),
            'deals' => $this->dealRepository->obtainAllDealsForParticipant($participant),
        ];
    }
}
