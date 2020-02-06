<?php

namespace kissj\Event;

class EventService {/*
    private $eventRepository;

    public function __construct(EventRepository $eventRepository) {
        $this->eventRepository = $eventRepository;
    }

    public function isEventDetailsValid(
        ?string $slug,
        ?string $readableName,
        ?string $accountNumber,
        ?bool $automaticPaymentPairing,
        ?int $prefixVariableSymbol,
        ?int $bankId,
        ?string $bankApi,
        ?bool $allowPatrols,
        ?int $maximalClosedPatrolsCount,
        ?int $minimalPatrolParticipantsCount,
        ?int $maximalPatrolParticipantsCount,
        ?bool $allowIsts,
        ?int $maximalClosedIstsCount
    ): bool {
        if (
            $slug === null
            || $readableName === null
            || $accountNumber === null
            || $automaticPaymentPairing === null
            || $allowPatrols === null
            || $allowIsts === null
        ) {
            return false;
        }

        // TODO check if correct
        if (!preg_match('/^[A-Za-z0-9\-]+$/', $slug)) {
            return false;
        }

        if ($automaticPaymentPairing && (
                $bankId === null
                || $bankApi === null
                || !is_numeric($prefixVariableSymbol))
        ) {
            return false;
        }

        if ($allowPatrols && (
                !is_numeric($maximalClosedPatrolsCount) ||
                !is_numeric($minimalPatrolParticipantsCount) ||
                !is_numeric($maximalPatrolParticipantsCount) ||
                $minimalPatrolParticipantsCount > $maximalPatrolParticipantsCount
            )
        ) {
            return false;
        }

        if ($allowIsts && !is_numeric($maximalClosedIstsCount)) {
            return false;
        }

        return true;
    }

    public function createEvent(
        string $slug,
        string $readableName,
        string $accountNumber,
        int $prefixVariableSymbol,
        bool $automaticPaymentPairing,
        int $bankId,
        string $bankApi,
        bool $allowPatrols,
        int $maximalClosedPatrolsCount,
        int $minimalPatrolParticipantsCount,
        int $maximalPatrolParticipantsCount,
        bool $allowIsts,
        int $maximalClosedIstsCount
    ): Event {
        $newEvent = new Event();
        $newEvent->slug = $slug;
        $newEvent->readableName = $readableName;
        $newEvent->accountNumber = $accountNumber;
        $newEvent->prefixVariableSymbol = $prefixVariableSymbol;
        $newEvent->automaticPaymentPairing = $automaticPaymentPairing;
        $newEvent->bankId = $bankId;
        $newEvent->bankApi = $bankApi;
        $newEvent->allowPatrols = $allowPatrols;
        $newEvent->maximalClosedPatrolsCount = $maximalClosedPatrolsCount;
        $newEvent->minimalPatrolParticipantsCount = $minimalPatrolParticipantsCount;
        $newEvent->maximalPatrolParticipantsCount = $maximalPatrolParticipantsCount;
        $newEvent->allowIsts = $allowIsts;
        $newEvent->maximalClosedIstsCount = $maximalClosedIstsCount;

        $this->eventRepository->persist($newEvent);

        return $newEvent;
    }

    public function getEventFromSlug(string $eventSlug): ?Event {
        $event = $this->eventRepository->findBy(['slug' => $eventSlug]);

        return $event[0] ?? null;
    }*/
}
