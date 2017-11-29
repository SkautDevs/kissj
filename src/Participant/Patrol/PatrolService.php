<?php

namespace kissj\Participant\Patrol;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;

class PatrolService {
	/** @var PatrolParticipantRepository */
	private $patrolParticipantRepository;
	/** @var PatrolLeaderRepository */
	private $patrolLeaderRepository;
	private $eventSettings;
	private $flashMessages;
	
	public function __construct(PatrolParticipantRepository $patrolParticipantRepository,
								PatrolLeaderRepository $patrolLeaderRepository,
								FlashMessagesInterface $flashMessages,
								$eventSettings) {
		$this->patrolParticipantRepository = $patrolParticipantRepository;
		$this->patrolLeaderRepository = $patrolLeaderRepository;
		$this->flashMessages = $flashMessages;
		$this->eventSettings = $eventSettings;
	}
	
	public function getPatrolLeader(User $user): PatrolLeader {
		if ($this->patrolLeaderRepository->countBy(['user' => $user]) === 0) {
			$patrolLeader = new PatrolLeader();
			$patrolLeader->user = $user;
			$patrolLeader->finished = false;
			$this->patrolLeaderRepository->persist($patrolLeader);
			return $patrolLeader;
		}
		
		$patrolLeader = $this->patrolLeaderRepository->findOneBy(['user' => $user]);
		return $patrolLeader;
	}
	
	private function isPatrolLeaderValid(PatrolLeader $patrolLeader): bool {
		return $this->isPatrolLeaderDetailsValid(
			$patrolLeader->getFirstName(),
			$patrolLeader->getLastName(),
			$patrolLeader->getAllergies(),
			($patrolLeader->getBirthDate() ? $patrolLeader->getBirthDate()->format('Y-m-d') : null),
			$patrolLeader->getBirthPlace(),
			$patrolLeader->getCountry(),
			$patrolLeader->getGender(),
			$patrolLeader->getPermanentResidence(),
			$patrolLeader->getScoutUnit(),
			$patrolLeader->getTelephoneNumber(),
			$patrolLeader->getEmail(),
			$patrolLeader->getFoodPreferences(),
			$patrolLeader->getCardPassportNumber(),
			$patrolLeader->getNotes(),
			$patrolLeader->getPatrolName()
		);
	}
	
	public function isPatrolLeaderDetailsValid(?string $firstName,
											   ?string $lastName,
											   ?string $allergies,
											   ?string $birthDate,
											   ?string $birthPlace,
											   ?string $country,
											   ?string $gender,
											   ?string $permanentResidence,
											   ?string $scoutUnit,
											   ?string $telephoneNumber,
											   ?string $email,
											   ?string $foodPreferences,
											   ?string $cardPassportNumber,
											   ?string $notes,
											   ?string $patrolName): bool {
		$validFlag = true;
		
		if (!empty($birthDate) && $birthDate !== date('Y-m-d', strtotime($birthDate))) {
			$validFlag = false;
		}
		// check for numbers and plus sight up front only
		if ((!empty ($telephoneNumber)) && preg_match('/^\+?\d+$/', $telephoneNumber) === 0) {
			$validFlag = false;
		}
		if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$validFlag = false;
		}
		
		return $validFlag;
	}
	
	public function editPatrolLeaderInfo(PatrolLeader $patrolLeader,
										 ?string $firstName,
										 ?string $lastName,
										 ?string $allergies,
										 ?string $birthDate,
										 ?string $birthPlace,
										 ?string $country,
										 ?string $gender,
										 ?string $permanentResidence,
										 ?string $scoutUnit,
										 ?string $telephoneNumber,
										 ?string $email,
										 ?string $foodPreferences,
										 ?string $cardPassportNumber,
										 ?string $notes,
										 ?string $patrolName) {
		$patrolLeader->firstName = $firstName;
		$patrolLeader->lastName = $lastName;
		$patrolLeader->allergies = $allergies;
		$patrolLeader->birthDate = new \DateTime($birthDate);
		$patrolLeader->birthPlace = $birthPlace;
		$patrolLeader->country = $country;
		$patrolLeader->gender = $gender;
		$patrolLeader->permanentResidence = $permanentResidence;
		$patrolLeader->scoutUnit = $scoutUnit;
		$patrolLeader->telephoneNumber = $telephoneNumber;
		$patrolLeader->email = $email;
		$patrolLeader->foodPreferences = $foodPreferences;
		$patrolLeader->cardPassportNumber = $cardPassportNumber;
		$patrolLeader->notes = $notes;
		$patrolLeader->patrolName = $patrolName;
		
		$this->patrolLeaderRepository->persist($patrolLeader);
	}
	
	public function getAllParticipantsBelongsPatrolLeader(PatrolLeader $patrolLeader): array {
		return $this->patrolParticipantRepository->findBy(['patrolleader' => $patrolLeader]);
	}
	
	public function addPatrolParticipant(PatrolLeader $patrolLeader): PatrolParticipant {
		$patrolParticipant = new PatrolParticipant();
		$patrolParticipant->patrolLeader = $patrolLeader;
		
		$this->patrolParticipantRepository->persist($patrolParticipant);
		
		return $patrolParticipant;
	}
	
	public function getPatrolParticipant(int $patrolParticipantId): PatrolParticipant {
		$patrolParticipant = $this->patrolParticipantRepository->findOneBy(['id' => $patrolParticipantId]);
		
		return $patrolParticipant;
	}
	
	private function isPatrolParticipantValid(PatrolParticipant $participant): bool {
		return $this->isPatrolParticipantDetailsValid($participant->getFirstName(),
			$participant->getLastName(),
			$participant->getAllergies(),
			($participant->getBirthDate() ? $participant->getBirthDate()->format('Y-m-d') : null),
			$participant->getBirthPlace(),
			$participant->getCountry(),
			$participant->getGender(),
			$participant->getPermanentResidence(),
			$participant->getScoutUnit(),
			$participant->getTelephoneNumber(),
			$participant->getEmail(),
			$participant->getFoodPreferences(),
			$participant->getCardPassportNumber(),
			$participant->getNotes()
		);
	}
	
	public function isPatrolParticipantDetailsValid(?string $firstName,
													?string $lastName,
													?string $allergies,
													?string $birthDate,
													?string $birthPlace,
													?string $country,
													?string $gender,
													?string $permanentResidence,
													?string $scoutUnit,
													?string $telephoneNumber,
													?string $email,
													?string $foodPreferences,
													?string $cardPassportNumber,
													?string $notes): bool {
		$validFlag = true;
		
		if (!empty($birthDate) && $birthDate !== date('Y-m-d', strtotime($birthDate))) {
			$validFlag = false;
		}
		// check for numbers and plus sight up front only
		if ((!empty ($telephoneNumber)) && preg_match('/^\+?\d+$/', $telephoneNumber) === 0) {
			$validFlag = false;
		}
		if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
			$validFlag = false;
		}
		
		return $validFlag;
	}
	
	public function editPatrolParticipant(PatrolParticipant $patrolParticipant,
										  ?string $firstName,
										  ?string $lastName,
										  ?string $allergies,
										  ?string $birthDate,
										  ?string $birthPlace,
										  ?string $country,
										  ?string $gender,
										  ?string $permanentResidence,
										  ?string $scoutUnit,
										  ?string $telephoneNumber,
										  ?string $email,
										  ?string $foodPreferences,
										  ?string $cardPassportNumber,
										  ?string $notes) {
		$patrolParticipant->firstName = $firstName;
		$patrolParticipant->lastName = $lastName;
		$patrolParticipant->allergies = $allergies;
		$patrolParticipant->birthDate = new \DateTime($birthDate);
		$patrolParticipant->birthPlace = $birthPlace;
		$patrolParticipant->country = $country;
		$patrolParticipant->gender = $gender;
		$patrolParticipant->permanentResidence = $permanentResidence;
		$patrolParticipant->scoutUnit = $scoutUnit;
		$patrolParticipant->telephoneNumber = $telephoneNumber;
		$patrolParticipant->email = $email;
		$patrolParticipant->foodPreferences = $foodPreferences;
		$patrolParticipant->cardPassportNumber = $cardPassportNumber;
		$patrolParticipant->notes = $notes;
		
		$this->patrolParticipantRepository->persist($patrolParticipant);
	}
	
	public function deletePatrolParticipant(PatrolParticipant $patrolParticipant) {
		$this->patrolParticipantRepository->delete($patrolParticipant);
	}
	
	public function patrolParticipantBelongsPatrolLeader(PatrolParticipant $patrolParticipant,
														 PatrolLeader $patrolLeader): bool {
		return $patrolParticipant->patrolLeader->id === $patrolLeader->id;
	}
	
	public function isCloseRegistrationValid(PatrolLeader $patrolLeader): bool {
		$validityFlag = true;
		if (!$this->isPatrolLeaderValid($patrolLeader)) {
			$this->flashMessages->warning('Údaje Patrol Leadera nejsou kompletní');
			$validityFlag = false;
		}
		$participants = $this->getAllParticipantsBelongsPatrolLeader($patrolLeader);
		$participantsCount = count($participants);
		if ($participantsCount < $this->eventSettings['minimalPatrolParticipantsCount']) {
			$this->flashMessages->warning('Účastníků je příliš málo - je jich jen '.$participantsCount.' z '.$this->eventSettings['minimalPatrolParticipantsCount']);
			$validityFlag = false;
		}
		if ($participantsCount > $this->eventSettings['maximalPatrolParticipantsCount']) {
			$this->flashMessages->warning('Účastníků je moc - je jich '.$participantsCount.' místo '.$this->eventSettings['maximalPatrolParticipantsCount']);
			$validityFlag = false;
		}
		foreach ($participants as $participant) {
			if (!$this->isPatrolParticipantValid($participant)) {
				$participantName = $participant->getFirstName().' '.$participant->getLastName();
				$this->flashMessages->warning('Údaje účastníka jménem '.$participantName.' nejsou kompletní');
				$validityFlag = false;
			}
		}
		if ($this->getClosedPatrolsCount() > $this->eventSettings['maximalClosedPatrolsCount']) {
			$this->flashMessages->warning('Je uzavřeno maximální počet patrol. Počkej prosím na zvýšení limitu patrol. ');
			$validityFlag = false;
		}
		
		return $validityFlag;
	}
	
	private function getClosedPatrolsCount(): int {
		// TODO implement
		return 25;
	}
	
	public function closeRegistration(PatrolLeader $patrolLeader) {
		$patrolLeader->finished = true;
		$this->patrolLeaderRepository->persist($patrolLeader);
	}
}