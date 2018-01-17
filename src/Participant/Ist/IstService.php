<?php

namespace kissj\Participant\Ist;

use kissj\Orm\Relation;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Mailer\MailerInterface;
use kissj\Payment\PaymentRepository;
use Slim\Views\Twig;
use kissj\Payment\Payment;
use kissj\User\Role;
use kissj\User\RoleRepository;
use kissj\User\RoleService;
use kissj\User\User;

class IstService {
	/** @var IstRepository */
	private $istRepository;
	/** @var RoleRepository */
	private $roleRepository;
	/** @var PaymentRepository */
	private $paymentRepository;
	private $roleService;
	private $flashMessages;
	private $renderer;
	private $mailer;
	private $eventSettings;
	
	public function __construct(IstRepository $istRepository,
								RoleRepository $roleRepository,
								PaymentRepository $paymentRepository,
								RoleService $userStatusService,
								FlashMessagesInterface $flashMessages,
								MailerInterface $mailer,
								Twig $renderer,
								$eventSettings,
								$eventName = 'cej2018'
	) {
		$this->istRepository = $istRepository;
		$this->roleRepository = $roleRepository;
		$this->paymentRepository = $paymentRepository;
		$this->roleService = $userStatusService;
		$this->flashMessages = $flashMessages;
		$this->mailer = $mailer;
		$this->renderer = $renderer;
		$this->eventSettings = $eventSettings;
		$this->eventName = $eventName;
	}
	
	public function getIst(User $user): Ist {
		if ($this->istRepository->countBy(['user' => $user]) === 0) {
			$ist = new Ist();
			$ist->user = $user;
			$this->istRepository->persist($ist);
		}
		
		$ist = $this->istRepository->findOneBy(['user' => $user]);
		return $ist;
	}
	
	public function getIstFromId(int $istId): Ist {
		return $this->istRepository->findOneBy(['id' => $istId]);
	}
	
	public function getAllClosedIsts(): array {
		$closedIsts = $this->roleRepository->findBy([
			'event' => $this->eventName,
			'name' => 'ist',
			'status' => 'closed']);
		$ists = [];
		/** @var Role $closedIst */
		foreach ($closedIsts as $closedIst) {
			$ists[] = $this->istRepository->findOneBy(['userId' => $closedIst->user->id]);
		}
		
		return $ists;
	}
	
	public function getAllApprovedIstsWithPayment(): array {
		$approvedIsts = $this->roleRepository->findBy([
			'event' => $this->eventName,
			'name' => 'ist',
			'status' => 'approved']);
		$ists = [];
		/** @var Role $approvedIst */
		foreach ($approvedIsts as $approvedIst) {
			$ist['info'] = $this->istRepository->findOneBy(['user' => $approvedIst->user]);
			$ist['payment'] = $this->getOnePayment($ist['info']);
			$ists[] = $ist;
		}
		
		return $ists;
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
		
		
		if (is_null($firstName) || is_null($lastName) || is_null($birthDate) || is_null($birthPlace) || is_null($country) || is_null($gender) || is_null($permanentResidence) || is_null($scoutUnit) || is_null($telephoneNumber) || is_null($email) || is_null($languages) || is_null($arrivalDate) || is_null($leavingDate)) {
			$validFlag = false;
		}
		
		foreach ([$birthDate, $arrivalDate, $leavingDate] as $date) {
			if (!empty($date) && $date !== date('Y-m-d', strtotime($date))) {
				$validFlag = false;
				break;
			}
		}
		// check for numbers and plus sight up front only
		/*if ((!empty ($telephoneNumber)) && preg_match('/^\+?\d+$/', $telephoneNumber) === 0) {
			$validFlag = false;
		}*/
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
			$this->flashMessages->warning('Nelze uzavřít registraci - údaje IST nejsou kompletní');
			$validityFlag = false;
		}
		if ($this->getClosedIstsCount() >= $this->eventSettings['maximalClosedIstsCount']) {
			$this->flashMessages->warning('Registraci už má uzavřenou maximální počet možných IST a ty se nevejdeš do počtu. Počkej prosím na zvýšení limitu pro IST.');
			$validityFlag = false;
		}
		
		return $validityFlag;
	}
	
	private function getClosedIstsCount(): int {
		return $this->roleRepository->countBy([
			'name' => 'ist',
			'event' => $this->eventName,
			'status' => new Relation('open', '!=')
		]);
	}
	
	public function getAllIstsStatistics(): array {
		$ists['limit'] = $this->eventSettings['maximalClosedIstsCount'];
		$ists['closed'] = $this->roleRepository->countBy([
			'name' => 'ist',
			'event' => $this->eventName,
			'status' => new Relation('closed', '==')
		]);
		
		$ists['approved'] = $this->roleRepository->countBy([
			'name' => 'ist',
			'event' => $this->eventName,
			'status' => new Relation('approved', '==')
		]);
		
		$ists['paid'] = $this->roleRepository->countBy([
			'name' => 'ist',
			'event' => $this->eventName,
			'status' => new Relation('paid', '==')
		]);
		
		return $ists;
	}
	
	public function sendPaymentByMail(Payment $payment, Ist $ist) {
		$message = $this->renderer->fetch('emails/payment-info.twig', [
			'eventName' => 'CEJ 2018',
			'accountNumber' => $payment->accountNumber,
			'price' => $payment->price,
			'currency' => 'Kč',
			'variableSymbol' => $payment->variableSymbol,
			'role' => $payment->role->name,
			'gender' => $ist->gender,
			
			'istFullName' => $ist->firstName.' '.$ist->lastName]);
		
		$this->mailer->sendMail($payment->role->user->email, 'Registrace CEJ 2018 - platební informace', $message);
	}
	
	public function sendDenialMail(Ist $ist, string $reason) {
		$message = $this->renderer->fetch('emails/denial.twig', [
			'eventName' => 'CEJ 2018',
			'role' => 'ist',
			'reason' => $reason,
		]);
		
		$this->mailer->sendMail($ist->user->email, 'Registrace CEJ 2018 - zamítnutí registrace', $message);
	}
	
	// TODO make this more clever
	public function getOnePayment(Ist $ist): ?Payment {
		if ($this->paymentRepository->isExisting(['roleId' => $this->roleRepository->findOneBy(['userId' => $ist->user->id, 'event' => 'cej2018'])])) {
			return $this->paymentRepository->findOneBy(['roleId' => $this->roleRepository->findOneBy(['userId' => $ist->user->id, 'event' => 'cej2018'])]);
		} else return null;
	}
	
	
	public function closeRegistration(Ist $ist) {
		/** @var Role $role */
		$role = $this->roleRepository->findOneBy(['userId' => $ist->user->id]);
		$role->status = $this->roleService->getCloseStatus();
		$this->roleRepository->persist($role);
	}
	
	public function approveIst(Ist $ist) {
		/** @var Role $role */
		$role = $this->roleRepository->findOneBy(['userId' => $ist->user->id]);
		$role->status = $this->roleService->getApproveStatus();
		$this->roleRepository->persist($role);
	}
	
	public function openIst(Ist $ist) {
		/** @var Role $role */
		$role = $this->roleRepository->findOneBy(['userId' => $ist->user->id]);
		$role->status = $this->roleService->getOpenStatus();
		$this->roleRepository->persist($role);
	}
}