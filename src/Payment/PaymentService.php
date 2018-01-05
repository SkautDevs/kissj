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
	private $logger;
	
	/** @var PaymentRepository */
	private $paymentRepository;
	/** @var RoleRepository */
	private $roleRepository;
	/** @var UserRepository */
	private $userRepository;
	
	public function __construct(array $paymentsSettings,
								PaymentRepository $paymentRepository,
								RoleRepository $roleRepository,
								UserRepository $userRepository,
								MailerInterface $mailer,
								Twig $renderer,
								string $eventName,
								Random $random,
								Logger $logger) {
		$this->settings = $paymentsSettings;
		$this->paymentRepository = $paymentRepository;
		$this->roleRepository = $roleRepository;
		$this->mailer = $mailer;
		$this->renderer = $renderer;
		$this->eventName = $eventName;
		$this->random = $random;
		$this->logger = $logger;
	}
	
	public function createNewPayment(Role $role): Payment {
		$newVS = $this->generateVariableSymbol($this->settings['prefixVariableSymbol']);
		$price = $this->getPriceFor($role);
		
		$newPayment = new Payment();
		$newPayment->event = $this->eventName;
		$newPayment->variableSymbol = $newVS;
		$newPayment->price = $price;
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
				return 2900;
			case 'patrol-leader':
				return 57000;
			default:
				throw new \Exception('Unknown role: '.$role->name);
		}
	}
	
	public function getPayment(User $user, string $event) {
		/** @var Role $role */
		$role = $this->roleRepository->findOneBy(['user' => $user->id]);
		return $this->paymentRepository->findOneBy(['role' => $role->id]);
	}
	
	public function isPaymentValid(string $variableSymbol, string $price): bool {
		return !is_null($this->paymentRepository->findOneBy([
			'variableSymbol' => $variableSymbol,
			'price' => $price]));
	}
	
	# Jak vygenerovat hezci CSV z Money S3
	/* cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;0" | head -n1 > test.csv; cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;1" >> test.csv */
	
	public function setPaymentPaid(Payment $payment) {
		// set payment valid in DB
		$payment->status = 'paid';
		$this->paymentRepository->persist($payment);
		
		// get User from Payment
		$userEmail = $payment->user->email;
		
		// write action into log
		$this->logger->addInfo('Payment with ID: '.$payment->id.' is set to "paid"');
		
		// send mail to user
		$message = $this->renderer->fetch('emails/payment-successful.twig', ['eventName' => $this->eventName]);
		$subject = $this->eventName.' - platba úspěšně přijata!';
		$this->mailer->sendMail($userEmail, $subject, $message);
	}
}