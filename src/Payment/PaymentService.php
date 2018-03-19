<?php

namespace kissj\Payment;

use kissj\Mailer\MailerInterface;
use kissj\Random;
use kissj\User\Role;
use kissj\User\RoleRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use Monolog\Logger;
use Slim\Views\Twig;

class PaymentService {
	private $settings;
	private $mailer;
	private $renderer;
	private $eventName;
	private $random;
	
	/** @var PaymentRepository */
	private $paymentRepository;
	/** @var RoleRepository */
	private $roleRepository;
	
	public function __construct(array $paymentsSettings,
								PaymentRepository $paymentRepository,
								RoleRepository $roleRepository,
								MailerInterface $mailer,
								Twig $renderer,
								string $eventName,
								Random $random) {
		$this->settings = $paymentsSettings;
		$this->paymentRepository = $paymentRepository;
		$this->roleRepository = $roleRepository;
		$this->mailer = $mailer;
		$this->renderer = $renderer;
		$this->eventName = $eventName;
		$this->random = $random;
	}
	
	public function createNewPayment(Role $role, bool $extraScarf): Payment {
		$newVS = $this->generateVariableSymbol($this->settings['prefixVariableSymbol']);
		$newPayment = new Payment();
		$newPayment->event = $this->eventName;
		$newPayment->variableSymbol = $newVS;
		$newPayment->price = $this->getPriceFor($role);
		// YAGNI!
		if ($extraScarf) {
			$newPayment->price += $this->settings['scarfPrice'];
		}
		$newPayment->currency = 'CZK';
		$newPayment->status = 'waiting';
		$newPayment->purpose = 'fee';
		$newPayment->role = $role;
		$newPayment->accountNumber = $this->settings['accountNumber'];
		
		$this->paymentRepository->persist($newPayment);
		
		return $newPayment;
	}
	
	private function generateVariableSymbol(string $prefix): string {
		do {
			$variableNumber = $prefix.str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
		} while ($this->isVariableNumberExisting($variableNumber));
		
		return $variableNumber;
	}
	
	private function isVariableNumberExisting(string $variableNumber): bool {
		$isExisting = $this->paymentRepository->isExisting(['variableSymbol' => $variableNumber]);
		return $isExisting;
	}
	
	private function getPriceFor(Role $role): int {
		switch ($role->name) {
			case 'ist':
				// před 1.7. 300, při a po 1.7. 450
				return 300;
			default:
				throw new \Exception('Unknown role: '.$role->name);
		}
	}
	
	public function getPaymentFromId(int $paymentId) {
		return $this->paymentRepository->findOneBy(['id' => $paymentId]);
	}
	
	public function isPaymentValid(string $variableSymbol, string $price): bool {
		return !is_null($this->paymentRepository->findOneBy([
			'variableSymbol' => $variableSymbol,
			'price' => $price]));
	}
	
	# Jak vygenerovat hezci CSV z Money S3
	/* cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;0" | head -n1 > test.csv; cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;1" >> test.csv */
	
	// TODO make separate function for mail sending
	public function setPaymentPaid(Payment $payment) {
		// set payment paid in DB
		$payment->status = 'paid';
		$this->paymentRepository->persist($payment);
		
		// set role as paid
		$role = $payment->role;
		$role->status = 'paid';
		$this->roleRepository->persist($role);
		
		// send mail to user
		$message = $this->renderer->fetch('emails/payment-successful.twig', ['eventName' => 'Korbo 2018', 'roleName' => $role->name]);
		$subject = 'Registrace Korbo 2018 - platba úspěšně přijata!';
		$this->mailer->sendMail($role->user->email, $subject, $message);
	}
}