<?php

namespace kissj\Participant\Patrol;

use kissj\User\User;


interface PatrolServiceInterface {
	
	public function getPatrolLeader(User $user): PatrolLeader;
	
	public function isPatrolLeaderDetailsValid(string $firstName,
											   string $lastName,
											   string $allergies,
											   string $birthDate,
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
											   string $patrolName): bool;
	
	public function editPatrolLeaderInfo(PatrolLeader $patrolLeader,
										 string $firstName,
										 string $lastName,
										 string $allergies,
										 string $birthDate,
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
		// PatrolLeader specific
										 string $patrolName
	);
	
	public function getAllParticipantsBelongsPatrolLeader(PatrolLeader $patrolLeader): array;
	
	public function addPatrolParticipant(PatrolLeader $patrolLeader): PatrolParticipant;
	
	public function getPatrolParticipant(int $patrolParticipantId): PatrolParticipant;
	
	public function isPatrolParticipantDetailsValid(?string $firstName,
													?string $lastName,
													?string $allergies,
													?\DateTime $birthDate,
													?string $birthPlace,
													?string $country,
													?string $gender,
													?string $permanentResidence,
													?string $scoutUnit,
													?string $telephoneNumber,
													?string $email,
													?string $foodPreferences,
													?string $cardPassportNumber,
													?string $notes): bool;
	
	public function editPatrolParticipant(PatrolParticipant $patrolParticipant,
										  ?string $firstName,
										  ?string $lastName,
										  ?string $allergies,
										  ?\DateTime $birthDate,
										  ?string $birthPlace,
										  ?string $country,
										  ?string $gender,
										  ?string $permanentResidence,
										  ?string $scoutUnit,
										  ?string $telephoneNumber,
										  ?string $email,
										  ?string $foodPreferences,
										  ?string $cardPassportNumber,
										  ?string $notes);
	
	public function deletePatrolParticipant(PatrolParticipant $patrolParticipant);
	
	public function participantBelongsPatrolLeader(PatrolParticipant $patrolParticipant,
												   PatrolLeader $patrolLeader): bool;
	
	public function closeRegistration(PatrolLeader $patrolLeader);
}