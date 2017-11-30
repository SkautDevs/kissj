<?php

namespace kissj\Participant\Ist;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\Role;
use kissj\User\RoleRepository;
use kissj\User\RoleService;
use kissj\User\User;

class IstService {
	/** @var IstRepository */
	private $istRepository;
	/** @var \kissj\User\RoleRepository */
	private $roleRepository;
	private $roleService;
	private $flashMessages;
	private $eventSettings;
	
	public function __construct(IstRepository $istRepository,
								RoleRepository $roleRepository,
								RoleService $userStatusService,
								FlashMessagesInterface $flashMessages,
								$eventSettings
								) {
		$this->istRepository = $istRepository;
		$this->roleRepository = $roleRepository;
		$this->roleService = $userStatusService;
		$this->flashMessages = $flashMessages;
		$this->eventSettings = $eventSettings;
		
	}
	
	public function getIst(User $user): Ist {
		if ($this->istRepository->countBy(['user' => $user]) === 0) {
			$ist = new Ist();
			$ist->user = $user;
			$this->istRepository->persist($ist);
			return $ist;
		}
		
		$ist = $this->istRepository->findOneBy(['user' => $user]);
		return $ist;
	}
	
	private function isIstValid(Ist $ist): bool {
		return $this->isIstDetailsValid(
			$ist->firstName,
			$ist->lastName,
			$ist->allergies,
			($ist->birthDate ? $ist->birthDate->format('Y-m-d') : null),
			$ist->birthPlace,
			$ist->country,
			$ist->gender,
			$ist->permanentResidence,
			$ist->scoutUnit,
			$ist->telephoneNumber,
			$ist->email,
			$ist->foodPreferences,
			$ist->cardPassportNumber,
			$ist->notes,
			$ist->workPreferences,
			$ist->skills,
			$ist->languages,
			($ist->arrivalDate ? $ist->arrivalDate->format('Y-m-d') : null),
			($ist->leavingDate ? $ist->leavingDate->format('Y-m-d') : null),
			$ist->carRegistrationPlate);
	}
	
	public function isIstDetailsValid(?string $firstName,
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
	
									  ?string $workPreferences,
									  ?string $skills,
									  ?string $languages,
									  ?string $arrivalDate,
									  ?string $leavingDate,
									  ?string $carRegistrationPlate
	): bool {
		$validFlag = true;
		
		foreach ([$birthDate, $arrivalDate, $leavingDate] as $date) {
			if (!empty($date) && $date !== date('Y-m-d', strtotime($date))) {
				$validFlag = false;
				break;
			}
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
	
	public function editIstInfo(Ist $ist,
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
	
								?string $workPreferences,
								?string $skills,
								?string $languages,
								?string $arrivalDate,
								?string $leavingDate,
								?string $carRegistrationPlate) {
		$ist->firstName = $firstName;
		$ist->lastName = $lastName;
		$ist->allergies = $allergies;
		$ist->birthDate = new \DateTime($birthDate);
		$ist->birthPlace = $birthPlace;
		$ist->country = $country;
		$ist->gender = $gender;
		$ist->permanentResidence = $permanentResidence;
		$ist->scoutUnit = $scoutUnit;
		$ist->telephoneNumber = $telephoneNumber;
		$ist->email = $email;
		$ist->foodPreferences = $foodPreferences;
		$ist->cardPassportNumber = $cardPassportNumber;
		$ist->notes = $notes;
		
		$ist->workPreferences = $workPreferences;
		$ist->skills = $skills;
		$ist->languages = $languages;
		$ist->arrivalDate = new \DateTime($arrivalDate);
		$ist->leavingDate = new \DateTime($leavingDate);
		$ist->carRegistrationPlate = $carRegistrationPlate;
		
		$this->istRepository->persist($ist);
	}
	
	public function isCloseRegistrationValid(Ist $ist): bool {
		$validityFlag = true;
		if (!$this->isIstValid($ist)) {
			$this->flashMessages->warning('Údaje IST nejsou kompletní');
			$validityFlag = false;
		}
		if ($this->getClosedIstsCount() > $this->eventSettings['maximalClosedIstsCount']) {
			$this->flashMessages->warning('Je zaregistrovaných maximální počet IST. Počkej prosím na zvýšení limitu pro IST.');
			$validityFlag = false;
		}
		
		return $validityFlag;
	}
	
	private function getClosedIstsCount(): int {
		// TODO implement
		return 100;
	}
	
	public function closeRegistration(Ist $ist) {
		/** @var Role $role */
		$role = $this->roleRepository->findOneBy(['userId' => $ist->user->id]);
		$role->status = $this->roleService->getNextStatus($role->status);
		$this->roleRepository->persist($role);
	}
}