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
	/** @var IstRepository */
	private $istRepository;
	
	/** @var RoleRepository */
	private $roleRepository;
	
	public function __construct(IstRepository $istRepository,
								RoleRepository $roleRepository) {
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
		
		/** @var Ist[] $ists */
		$ists = $this->istRepository->findBy([
			'userId' => new Relation($istUserIds, 'IN')
		]);
		
		$rows = [];
		foreach ($ists as $ist) {
			$rows[] = [
				$ist->firstName,
				$ist->lastName,
				$ist->birthDate == null ? '' : $ist->birthDate->format('Y-m-d'),
				$ist->allergies,
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
		/** @var Ist[] $ists */
		$ists = $this->istRepository->findBy([
			'userId' => new Relation($istUserIds, 'IN')
		]);
		
		$rows = [];
		
		// nulls headers
		
		$rows[] = [
			'ID',
			'Křestní jméno',
			'Příjmení',
			'Přezdívka',
			'Gender',
			'Email',
			'Datum narození',
			'Addresa',
			'Zákonný zástupce do 18',
			'Šátek',
			'Poznámky',
		];
		
		foreach ($ists as $ist) {
			$rows[] = [
				$ist->id,
				$ist->firstName,
				$ist->lastName,
				$ist->nickname,
				$ist->gender,
				$ist->email,
				$ist->birthDate->format('d. m. Y'),
				$ist->permanentResidence,
				$ist->legalRepresestative,
				$ist->scarf,
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