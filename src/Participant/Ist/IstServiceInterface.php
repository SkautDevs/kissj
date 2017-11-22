<?php

namespace kissj\Participant\Ist;


interface IstServiceInterface {
	public function addIstInfo(Ist $ist,
							   string $firstName,
							   string $lastName,
							   string $allergies,
							   \DateTime $birthDate,
							   string $birthPlace,
							   string $country,
							   string $gender,
							   string $permanentResidence,
							   string $scoutUnit,
							   string $telephoneNumber,
							   string $email,
							   string $foodPreferences,
							   string $cardPassportNumber,
							   string $notes,
		// IST specific
							   string $workPreferences,
							   string $skills,
							   string $languages,
							   \DateTime $arrivalDate,
							   \DateTime $leavingDate,
							   string $carRegistrationPlate
	);
	
	public function closeRegistration(Ist $ist);
}