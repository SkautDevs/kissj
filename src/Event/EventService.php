<?php

namespace kissj\Event;

use kissj\User\User;

class EventService {
	
	/** @var EventRepository */
	private $eventRepository;
	
	public function __construct(EventRepository $eventRepository) {
		$this->eventRepository = $eventRepository;
	}
	
	
	public function isEventDetailsValid(?string $slug,
										?string $readableName,
										?string $accountNumber,
										?int $prefixVariableSymbol,
										?bool $automaticPaymentPairing,
										?int $bankId,
										?string $bankApi,
										?bool $allowPatrols,
										?int $maximalClosedPatrolsCount,
										?int $minimalPatrolParticipantsCount,
										?int $maximalPatrolParticipantsCount,
										?bool $allowIsts,
										?int $maximalClosedIstsCount): bool {
		// TODO check nonempty
		// TODO regex slug
		// TODO conditional check if allowed then chech Patrols + ISTs
		// and probably some more
		
		return true;
	}
	
	public function createEvent(string $slug,
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
								int $maximalClosedIstsCount): Event {
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
	
	public function getEventFromSlug(string $eventSlug): Event {
		$event = new Event();
		// TODO
		return $event;
	}
}