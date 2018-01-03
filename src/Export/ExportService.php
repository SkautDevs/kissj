<?php

namespace kissj\Export;


use kissj\Orm\Relation;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\User\Role;
use kissj\User\RoleRepository;

class ExportService {

	/** @var PatrolParticipantRepository */
	private $patrolParticipantRepository;

	/** @var PatrolLeaderRepository */
	private $patrolLeaderRepository;

	/** @var IstRepository */
	private $istRepository;

	/** @var RoleRepository */
	private $roleRepository;

	public function __construct(PatrolParticipantRepository $patrolParticipantRepository,
	                            PatrolLeaderRepository $patrolLeaderRepository, IstRepository $istRepository,
	                            RoleRepository $roleRepository) {
		$this->patrolParticipantRepository = $patrolParticipantRepository;
		$this->patrolLeaderRepository = $patrolLeaderRepository;
		$this->istRepository = $istRepository;
		$this->roleRepository = $roleRepository;
	}

	public function medicalDataToCSV(string $event): array {
//		\dibi::test('SELECT * FROM article WHERE id IN %in', array(1,2,3))->test();

		/** @var Role[] $roles */
		$roles = $this->roleRepository->findBy(['event' => $event]);
		$patrolLeaderUserIds = [];
		$istUserIds = [];
		foreach ($roles as $role) {
			if ($role->name === 'patrol-leader') {
				$patrolLeaderUserIds[] = $role->user->id;
			} elseif ($role->name === 'ist') {
				$istUserIds[] = $role->user->id;
			};
		}
		/** @var PatrolLeader[] $patrolLeaders */
		$patrolLeaders = $this->patrolLeaderRepository->findBy([
			'userId' => new Relation($patrolLeaderUserIds, 'IN')
		]);

		$patrolLeaderIds = array_map(function(PatrolLeader $p) {return $p->id;}, $patrolLeaders);

		/** @var Ist[] $ists */
		$ists = $this->istRepository->findBy([
			'userId' => new Relation($istUserIds, 'IN')
		]);
		/** @var PatrolParticipant[] $partolParticipants */
		$partolParticipants = $this->patrolParticipantRepository->findBy([
			'patrolleaderId' => new Relation($patrolLeaderIds, 'IN')
		]);

		$rows = [];
		foreach ($patrolLeaders as $leader) {
			$rows[] = [$leader->firstName, $leader->lastName, $leader->birthDate == null ? '' : $leader->birthDate->format('Y-m-d'), $leader->allergies];
		}
		foreach ($ists as $ist) {
			$rows[] = [$ist->firstName, $ist->lastName, $ist->birthDate == null ? '' : $ist->birthDate->format('Y-m-d'), $ist->allergies];
		}
		foreach ($partolParticipants as $participant) {
			$rows[] = [$participant->firstName, $participant->lastName, $participant->birthDate == null ? '' : $participant->birthDate->format('Y-m-d'), $participant->allergies];
		}
		return $rows;
	}

}