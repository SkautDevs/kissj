<?php

namespace kissj\Participant\Ist;

use kissj\User\User;


interface IstServiceInterface {
	public function addIstInfo(Ist $ist,
							   string $firstName,
							   string $lastName,
							   string $allergies,
							   string $dateOfBirth,
							   string $permanentResidence,
							   string $telephoneNumber,
							   string $scoutUnit,
							   string $country,
							   string $notes
	);
	
	public function getIst(User $user): Ist;
	
	public function closeRegistration(Ist $patrolLeader);
}