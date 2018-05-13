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
use Slim\Http\Response;
use League\Csv\Reader;
use League\Csv\Writer;

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
								PatrolLeaderRepository $patrolLeaderRepository,
								IstRepository $istRepository,
								RoleRepository $roleRepository) {
		$this->patrolParticipantRepository = $patrolParticipantRepository;
		$this->patrolLeaderRepository = $patrolLeaderRepository;
		$this->istRepository = $istRepository;
		$this->roleRepository = $roleRepository;
	}

	public function createCSVresponse(Response $response,
									  array $csvRows,
									  string $fileName,
									  bool $amendTimestamp = true) {
		if ($amendTimestamp) {
			$fileName .= '_'.date(DATE_ISO8601, time());
		}

		$response = $response->withHeader('Content-Type', 'text/csv');
		$response = $response->withHeader('Content-Disposition', 'attachment; filename="'.$fileName.'.csv";');
		$response = $response->withHeader('Expires', '0');
		$response = $response->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate');
		$response = $response->withHeader('Pragma', 'no-cache');

		$csv = Writer::createFromFileObject(new \SplTempFileObject());
		$csv->setDelimiter(',');
		$csv->setOutputBOM(Reader::BOM_UTF8);
		$csv->insertAll($csvRows);

		$body = $response->getBody();
		$body->write($csv->getContent());

		return $response->withBody($body);
	}

	public function logisticDataPatrolsToCSV(string $event): array {
		/** @var Role[] $roles */
		$roles = $this->getExportRoles($event);
		$patrolLeaderUserIds = [];
		foreach ($roles as $role) {
			if ($role->name === 'patrol-leader') {
				$patrolLeaderUserIds[] = $role->user->id;
			}
		}
		/** @var PatrolLeader[] $patrolLeaders */
		$patrolLeaders = $this->patrolLeaderRepository->findBy([
			'userId' => new Relation($patrolLeaderUserIds, 'IN')
		]);

		$patrolLeaderIds = array_map(function (PatrolLeader $p) {
			return $p->id;
		}, $patrolLeaders);

		/** @var PatrolParticipant[] $partolParticipants */
		$partolParticipants = $this->patrolParticipantRepository->findBy([
			'patrolleaderId' => new Relation($patrolLeaderIds, 'IN')
		]);

		$rows = [];
		foreach ($patrolLeaders as $leader) {
			$rows[] = [
				$leader->id,
				$leader->permanentResidence,
				$leader->country,
				$leader->firstName.' '.$leader->lastName,
				$leader->email,
			];
		}
		foreach ($partolParticipants as $participant) {
			$rows[] = [
				$participant->patrolLeader->id,
				$participant->permanentResidence,
				$participant->country,
			];
		}

		return $rows;
	}

	public function medicalDataToCSV(string $event): array {
		/** @var Role[] $roles */
		$roles = $this->getExportRoles($event);
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

		$patrolLeaderIds = array_map(function (PatrolLeader $p) {
			return $p->id;
		}, $patrolLeaders);

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
			$rows[] = [
				$leader->firstName,
				$leader->lastName,
				$leader->birthDate == null ? '' : $leader->birthDate->format('Y-m-d'),
				$leader->allergies,
			];
		}
		foreach ($ists as $ist) {
			$rows[] = [
				$ist->firstName,
				$ist->lastName,
				$ist->birthDate == null ? '' : $ist->birthDate->format('Y-m-d'),
				$ist->allergies,
			];
		}
		foreach ($partolParticipants as $participant) {
			$rows[] = [
				$participant->firstName,
				$participant->lastName,
				$participant->birthDate == null ? '' : $participant->birthDate->format('Y-m-d'),
				$participant->allergies,
			];
		}
		return $rows;
	}

	public function paidContactDataToCSV(string $event): array {
		// TODO now IST only - add PL
		/** @var Role[] $roles */
		$roles = $this->roleRepository->findByMultiple([
			['event' => $event,],
			['status' => 'paid',],
		]);
		$istUserIds = [];
		foreach ($roles as $role) {
			$istUserIds[] = $role->user->id;
		}
		/** @var Ist[] $ists */
		$ists = $this->istRepository->findBy([
			'userId' => new Relation($istUserIds, 'IN')
		]);

		$rows = [];

		$rows[] = [
			'ID',
			'Křestní jméno',
			'Příjmení',
			'Přezdívka',
			'Email',
		];

		foreach ($ists as $ist) {
			$rows[] = [
				$ist->id,
				$ist->firstName,
				$ist->lastName,
				$ist->nickname,
				$ist->email,
			];
		}
		return $rows;
	}

	public function allRegistrationDataToCSV(string $event): array {
		/** @var Role[] $roles */
		$roles = $this->getExportRoles($event);
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

		$patrolLeaderIds = array_map(function (PatrolLeader $p) {
			return $p->id;
		}, $patrolLeaders);

		/** @var Ist[] $ists */
		$ists = $this->istRepository->findBy([
			'userId' => new Relation($istUserIds, 'IN')
		]);

		$rows = [];

		// nulls headers

		$rows[] = [
			'No.',
			'Patrol',
			'Role',
			'First Name',
			'Last Name',
			'Nationality',
			'Gender',
			'Email Address',
			'Phone Number',
			'Birthdate',
			'Birthplace',
			'Country',
			'Zip code',
			'City',
			'Address',
			'Passport / ID number',
			'Food preferences',
			'Ability to swim',
			'Needed medicine',
			'Allergies',
			'Patrol name',
			'Area',
			'Skills',
			'Arrival Date',
			'Leaving Date',
			'Car Registration Number',
			'Additional info',
		];
		$counter = 0;
		$patrolCounter = 0;
		// first leaders with their participants, ...
		foreach ($patrolLeaders as $leader) {
			$rows[] = [
				++$counter,
				++$patrolCounter,
				'Patrol Leader',
				$leader->firstName,
				$leader->lastName,
				$leader->country,
				$leader->gender,
				$leader->email,
				$leader->telephoneNumber,
				$leader->birthDate->format('d. m. Y'),
				$leader->birthPlace,
				$leader->country,
				'', // ZIP code
				'', // city
				$leader->permanentResidence,
				$leader->cardPassportNumber,
				$leader->foodPreferences,
				'yes', // ability to swim
				'via. medical info', // meeded medicine
				$leader->allergies,
				$leader->patrolName,
				'', // Area',
				'', // Skills',
				'', // Arrival Date',
				'', // Leaving Date',
				'', // Car Registration Number',
				$leader->notes,
			];

			/** @var PatrolParticipant[] $partolParticipants */
			$partolParticipants = $this->patrolParticipantRepository->findBy([
				'patrolleaderId' => $leader->id]);

			foreach ($partolParticipants as $participant) {
				$rows[] = [
					++$counter,
					$patrolCounter,
					'Patrol Participant',
					$participant->firstName,
					$participant->lastName,
					$participant->country,
					$participant->gender,
					$participant->email,
					$participant->telephoneNumber,
					$participant->birthDate->format('d. m. Y'),
					$participant->birthPlace,
					$participant->country,
					'', // ZIP code
					'', // city
					$participant->permanentResidence,
					$participant->cardPassportNumber,
					$participant->foodPreferences,
					'yes', // ability to swim
					'via. medical info', // meeded medicine
					$participant->allergies,
					$leader->patrolName,
					'', // 'Area',
					'', // 'Skills',
					'', // 'Arrival Date',
					'', // 'Leaving Date',
					'', // 'Car Registration Number',
					$participant->notes,
				];
			}
		}

		// ...second ISTs
		$counter = 0; // from the top
		foreach ($ists as $ist) {
			$rows[] = [
				++$counter,
				'', // patrol counter
				'IST',
				$ist->firstName,
				$ist->lastName,
				$ist->country,
				$ist->gender,
				$ist->email,
				$ist->telephoneNumber,
				$ist->birthDate->format('d. m. Y'),
				$ist->birthPlace,
				$ist->country,
				'', // ZIP code
				'', // city
				$ist->permanentResidence,
				$ist->cardPassportNumber,
				$ist->foodPreferences,
				'yes', // ability to swim
				'via. medical info', // meeded medicine
				$ist->allergies,
				'', // patrol name
				$ist->workPreferences,
				$ist->skills,
				$ist->arrivalDate->format('d. m. Y'),
				$ist->leavingDate->format('d. m. Y'),
				$ist->carRegistrationPlate,
				$ist->notes,
			];
		}
		return $rows;
	}

	private function getExportRoles(string $event): array {
		// TODO rewirte with use of whitelist ('closed', 'approved', 'paid' only)
		return $this->roleRepository->findByMultiple([
			['event' => $event,],
			['status' => new Relation('admin', '!='),],
			['status' => new Relation('open', '!='),],
		]);
	}
}