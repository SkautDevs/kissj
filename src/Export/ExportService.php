<?php

namespace kissj\Export;


use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipantRepository;
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

	public function medicalDataToCSV(string $event): string {
		$roles = $this->roleRepository->findBy(['event' => $event]);
	}

}