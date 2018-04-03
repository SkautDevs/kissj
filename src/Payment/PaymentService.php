<?php

namespace kissj\Payment;

use h4kuna\Fio\FioRead;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Mailer\MailerInterface;
use kissj\Random;
use kissj\User\Role;
use kissj\User\RoleRepository;
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
	/** @var FioRead */
	private $paymentAutoMatcherFio;
	/** @var RoleRepository */
	private $roleRepository;
	/** @var FlashMessagesInterface $flashMessages */
	private $flashMessages;
	/** @var Logger $logger */
	private $logger;

	public function __construct(array $paymentsSettings,
								PaymentRepository $paymentRepository,
								FioRead $paymentAutoMatcherFio,
								RoleRepository $roleRepository,
								MailerInterface $mailer,
								Twig $renderer,
								FlashMessagesInterface $flashMessages,
								Logger $logger,
								string $eventName,
								Random $random) {
		$this->settings = $paymentsSettings;
		$this->paymentRepository = $paymentRepository;
		$this->paymentAutoMatcherFio = $paymentAutoMatcherFio;
		$this->roleRepository = $roleRepository;
		$this->mailer = $mailer;
		$this->renderer = $renderer;
		$this->flashMessages = $flashMessages;
		$this->logger = $logger;
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
		$newPayment->generatedDate = new \DateTime();

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
	public function setPaymentPaid(Payment $payment): void {
		// set payment paid in DB
		$payment->status = 'paid';
		$this->paymentRepository->persist($payment);

		// set role as paid
		$role = $payment->role;
		$role->status = 'paid';
		$this->roleRepository->persist($role);

		$this->sendSuccesfulPaymentEmail($role);
	}

	public function pairNewPayments($approvedIstPayments) {
		// get list of new payments
		$list = $this->paymentAutoMatcherFio->lastDownload();

		// iterate and try find a match
		$counterSetPaid = 0;
		$counterUnknownPayment = 0;
		/** @var $transaction \h4kuna\Fio\Response\Read\Transaction */
		foreach ($list as $transaction) {
			$paidFlag = false;
			/** @var Payment $payment */
			foreach ($approvedIstPayments as $payment) {
				if ($payment->variableSymbol === $transaction->variableSymbol && $payment->price === $transaction->volume) {
					// match!
					$payment->status = 'paid';
					$this->paymentRepository->persist($payment);

					$this->sendSuccesfulPaymentEmail($payment->role);

					$this->logger->addInfo('Payment '.$payment->id.' is set to '.$payment->status.' automatically');
					$counterSetPaid++;
					$paidFlag = true;
					break;
				}
			}
			// nonrecognized transaction
			if ($paidFlag === false) {
				$counterUnknownPayment++;
				// TODO better system for this warning
				$this->flashMessages->warning(htmlspecialchars('Nerozeznaná platba: '.
					$transaction->volume.' Kč, VS: '.$transaction->variableSymbol.', poznámka: '.$transaction->note, ENT_QUOTES));
			}
		}

		// TODO better system for... :/
		// output counts
		$this->flashMessages->success('Spárováno '.$counterSetPaid.' plateb s transakcemi z banky, nerozeznáno '.
			$counterUnknownPayment.' bankovních transakcí. ');
	}

	public function setLastDate(string $date): void {
		$this->paymentAutoMatcherFio->setLastDate('2017-01-01');
	}

	private function sendSuccesfulPaymentEmail(Role $role) {
		// send mail to user
		$message = $this->renderer->fetch('emails/payment-successful.twig', []);
		$subject = 'Registrace KORBO 2018 - platba úspěšně přijata!';
		$this->mailer->sendMail($role->user->email, $subject, $message);
	}
}