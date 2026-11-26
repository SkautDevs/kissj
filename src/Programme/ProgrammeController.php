<?php

declare(strict_types=1);

namespace kissj\Programme;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Programme\Image\ProgrammeImageRepository;
use kissj\Programme\Image\ProgrammeImageService;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;

class ProgrammeController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ProgrammeEnrolmentRepository $programmeEnrolmentRepository,
        private readonly ParticipantRepository $participantRepository,
        private readonly ProgrammeImageRepository $programmeImageRepository,
        private readonly ProgrammeImageService $programmeImageService,
    ) {
    }

    public function list(Response $response, Event $event, User $user): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);
        $programmes = $this->programmeRepository->findActiveForRole($event, $participant->getRoleOrFail());
        $ownProgrammeIds = [];
        foreach ($this->programmeEnrolmentRepository->findActiveForParticipant($participant) as $enrolment) {
            $ownProgrammeIds[] = $enrolment->programme->id;
        }

        return $this->view->render($response, 'participant/programmes.twig', [
            'programmes' => $programmes,
            'ownProgrammeIds' => $ownProgrammeIds,
            'event' => $event,
        ]);
    }

    public function detail(Response $response, Event $event, User $user, int $programmeId): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);
        $programme = $this->programmeRepository->findActiveById($programmeId, $event);
        if ($programme === null || $programme->isForRole($participant->getRoleOrFail()) === false) {
            return $response->withStatus(404);
        }

        $isEnrolled = $this->programmeEnrolmentRepository->findActive($programme, $participant) !== null;

        return $this->view->render($response, 'participant/programmeDetail.twig', [
            'programme' => $programme,
            'isEnrolled' => $isEnrolled,
            'hasImage' => $this->programmeImageRepository->findFirstForProgramme($programme) !== null,
            'event' => $event,
        ]);
    }

    public function image(Response $response, Event $event, User $user, int $programmeId): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);
        $programme = $this->programmeRepository->findActiveById($programmeId, $event);
        if ($programme !== null && $programme->isForRole($participant->getRoleOrFail()) === false) {
            return $response->withStatus(404);
        }

        return $this->serveImage($response, $programme);
    }

    public function adminImage(Response $response, Event $event, int $programmeId): Response
    {
        return $this->serveImage($response, $this->programmeRepository->findActiveById($programmeId, $event));
    }

    private function serveImage(Response $response, ?Programme $programme): Response
    {
        $image = $programme === null ? null : $this->programmeImageRepository->findFirstForProgramme($programme);
        $file = $image === null ? null : $this->programmeImageService->openFull($image);
        if ($file === null) {
            return $response->withStatus(404);
        }

        return $this->withUntrustedFileSecurityHeaders($response)
            ->withHeader('Content-Type', $file->mimeContentType)
            ->withBody($file->stream);
    }
}
