<?php

declare(strict_types=1);

namespace kissj\Programme\Image;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Programme\ProgrammeRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProgrammeImageAdminController extends AbstractController
{
    public function __construct(
        private readonly ProgrammeRepository $programmeRepository,
        private readonly ProgrammeImageRepository $programmeImageRepository,
        private readonly ProgrammeImageService $programmeImageService,
    ) {
    }

    public function update(Request $request, Response $response, Event $event, int $programmeId, int $imageId): Response
    {
        $this->programmeImageService->updateAlt(
            $this->getImage($event, $programmeId, $imageId),
            trim($this->getParameterFromBody($request, 'alt')),
        );
        $this->flashMessages->success('flash.success.programmeImageSaved');

        return $this->redirectToEdit($request, $response, $programmeId);
    }

    public function move(
        Request $request,
        Response $response,
        Event $event,
        int $programmeId,
        int $imageId,
        string $direction,
    ): Response {
        $this->programmeImageService->move($this->getImage($event, $programmeId, $imageId), $direction);

        return $this->redirectToEdit($request, $response, $programmeId);
    }

    public function delete(Request $request, Response $response, Event $event, int $programmeId, int $imageId): Response
    {
        $this->programmeImageService->delete($this->getImage($event, $programmeId, $imageId));
        $this->flashMessages->success('flash.success.programmeImageDeleted');

        return $this->redirectToEdit($request, $response, $programmeId);
    }

    public function full(Response $response, Event $event, int $programmeId): Response
    {
        $programme = $this->programmeRepository->findActiveById($programmeId, $event);
        $image = $programme === null ? null : $this->programmeImageRepository->findFirstForProgramme($programme);
        $file = $image === null ? null : $this->programmeImageService->openFull($image);
        if ($file === null) {
            return $response->withStatus(404);
        }

        return $this->withUntrustedFileSecurityHeaders($response)
            ->withHeader('Content-Type', $file->mimeContentType)
            ->withBody($file->stream);
    }

    /**
     * Serves events without the programme API too, unlike the public thumb route.
     */
    public function thumb(Response $response, Event $event, int $programmeId, int $imageId): Response
    {
        $file = $this->programmeImageService->openThumb($this->getImage($event, $programmeId, $imageId));
        if ($file === null) {
            return $response->withStatus(404);
        }

        return $this->withUntrustedFileSecurityHeaders($response)
            ->withHeader('Content-Type', $file->mimeContentType)
            ->withBody($file->stream);
    }

    private function getImage(Event $event, int $programmeId, int $imageId): ProgrammeImage
    {
        $programme = $this->programmeRepository->getActiveById($programmeId, $event);

        return $this->programmeImageRepository->getForProgrammeById($imageId, $programme);
    }

    private function redirectToEdit(Request $request, Response $response, int $programmeId): Response
    {
        return $this->redirect($request, $response, 'admin-programme-edit', ['programmeId' => (string)$programmeId]);
    }
}
