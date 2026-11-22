<?php

declare(strict_types=1);

namespace kissj\Programme\Place;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Programme\DTO\ProgrammePlaceCountsDTO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProgrammePlaceAdminController extends AbstractController
{
    public function __construct(
        private readonly ProgrammePlaceRepository $programmePlaceRepository,
        private readonly ProgrammePlaceService $programmePlaceService,
    ) {
    }

    public function overview(Response $response, Event $event): Response
    {
        $places = $this->programmePlaceRepository->findForEvent($event);
        $countsByPlace = $this->programmePlaceRepository->countProgrammesByPlace($event);
        $counts = [];
        foreach ($places as $place) {
            $counts[$place->id] = $countsByPlace[$place->id] ?? new ProgrammePlaceCountsDTO(0, 0);
        }

        return $this->view->render($response, 'admin/programmePlaces-admin.twig', [
            'places' => $places,
            'counts' => $counts,
            'event' => $event,
        ]);
    }

    public function create(Request $request, Response $response, Event $event): Response
    {
        $name = $this->nameFromRequest($request);
        if ($name === '') {
            return $this->redirectWithError($request, $response, 'flash.error.programmePlaceNameRequired');
        }
        $coordinatesInput = trim($this->getParameterFromBody($request, 'coordinates'));
        $coordinates = Coordinates::tryParse($coordinatesInput);
        if ($coordinatesInput !== '' && $coordinates === null) {
            return $this->redirectWithError($request, $response, 'flash.error.programmePlaceInvalidCoordinates');
        }
        if ($this->programmePlaceService->create($event, $name, $coordinates) === false) {
            return $this->redirectWithError($request, $response, 'flash.error.programmePlaceNameTaken');
        }
        $this->flashMessages->success('flash.success.programmePlaceSaved');

        return $this->redirectToOverview($request, $response);
    }

    public function update(Request $request, Response $response, Event $event, int $placeId): Response
    {
        $place = $this->programmePlaceRepository->getForEventById($placeId, $event);

        $name = $this->nameFromRequest($request);
        if ($name === '') {
            return $this->redirectWithError($request, $response, 'flash.error.programmePlaceNameRequired');
        }
        $coordinatesInput = trim($this->getParameterFromBody($request, 'coordinates'));
        $coordinates = Coordinates::tryParse($coordinatesInput);
        if ($coordinatesInput !== '' && $coordinates === null) {
            return $this->redirectWithError($request, $response, 'flash.error.programmePlaceInvalidCoordinates');
        }

        if ($this->programmePlaceService->update($place, $name, $coordinates) === false) {
            return $this->redirectWithError($request, $response, 'flash.error.programmePlaceNameTaken');
        }
        $this->flashMessages->success('flash.success.programmePlaceSaved');

        return $this->redirectToOverview($request, $response);
    }

    public function delete(Request $request, Response $response, Event $event, int $placeId): Response
    {
        $place = $this->programmePlaceRepository->getForEventById($placeId, $event);

        if ($this->programmePlaceRepository->deleteUnlessReferenced($place) === false) {
            $this->flashMessages->error(
                'flash.error.programmePlaceHasProgrammes',
                ['%count%' => (string)$this->programmePlaceRepository->countProgrammes($place)],
            );

            return $this->redirectToOverview($request, $response);
        }
        $this->flashMessages->success('flash.success.programmePlaceDeleted');

        return $this->redirectToOverview($request, $response);
    }

    private function nameFromRequest(Request $request): string
    {
        return trim($this->getParameterFromBody($request, 'name'));
    }

    private function redirectWithError(Request $request, Response $response, string $translationKey): Response
    {
        $this->flashMessages->error($translationKey);

        return $this->redirectToOverview($request, $response);
    }

    private function redirectToOverview(Request $request, Response $response): Response
    {
        return $this->redirect($request, $response, 'admin-programme-places');
    }
}
