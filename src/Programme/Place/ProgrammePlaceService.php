<?php

declare(strict_types=1);

namespace kissj\Programme\Place;

use Dibi\UniqueConstraintViolationException;
use kissj\Event\Event;

readonly class ProgrammePlaceService
{
    public function __construct(
        private ProgrammePlaceRepository $programmePlaceRepository,
    ) {
    }

    public function findOrCreateByName(Event $event, string $name): ProgrammePlace
    {
        $name = trim($name);
        $existing = $this->programmePlaceRepository->findForEventByName($event, $name);
        if ($existing !== null) {
            return $existing;
        }

        return $this->programmePlaceRepository->create($event, $name, null);
    }

    public function create(Event $event, string $name, ?Coordinates $coordinates): bool
    {
        if ($this->programmePlaceRepository->findForEventByName($event, $name) !== null) {
            return false;
        }
        $this->programmePlaceRepository->create($event, $name, $coordinates);

        return true;
    }

    public function update(ProgrammePlace $place, string $name, ?Coordinates $coordinates): bool
    {
        $namesake = $this->programmePlaceRepository->findForEventByName($place->event, $name);
        if ($namesake !== null && $namesake->id !== $place->id) {
            return false;
        }

        $previousName = $place->name;
        $previousLat = $place->lat;
        $previousLng = $place->lng;
        try {
            $place->name = $name;
            $place->lat = $coordinates?->lat;
            $place->lng = $coordinates?->lng;
            $this->programmePlaceRepository->persist($place);
        } catch (UniqueConstraintViolationException) {
            // a concurrent request took the name between the check above and the write
            $place->name = $previousName;
            $place->lat = $previousLat;
            $place->lng = $previousLng;

            return false;
        }

        return true;
    }
}
