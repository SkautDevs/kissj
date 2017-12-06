<?php

namespace kissj\Payment;

use kissj\Mailer\MailerInterface;
use kissj\Random;
use kissj\User\User;
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
	
	function __construct(array $paymentsSettings,
						 PaymentRepository $paymentRepository,
						 MailerInterface $mailer,
						 Twig $renderer,
						 string $eventName,
						 Random $random,
						 Logger $logger) {
		$this->settings = $paymentsSettings;
		$this->mailer = $mailer;
		$this->renderer = $renderer;
		$this->eventName = $eventName;
		$this->random = $random;
		$this->logger = $logger;
	}
	
	function createNewPayment(User $user): Payment {
		$newVS = $this->random->generateVariableSymbol($this->settings['prefixVariableSymbol']);
		
		$newPayment = new Payment();
		$newPayment->event = $this->eventName;
		$newPayment->variableSymbol = $newVS;
		$newPayment->status = 'waiting';
		$newPayment->user = $user;
		
		$this->paymentRepository->persist($newPayment);
		
		return $newPayment;
	}
	
	// should we first create Payment object, or validate that from strings?
	function isPaymentValid(string $variableSymbol, string $price): bool {
		// TODO implement
	}
	
	function setPaymentPaid(Payment $payment) {
		// set payment valid in DB
		$payment->status = 'paid';
		$this->paymentRepository->persist($payment);
		
		// get User from Payment
		$userEmail = $payment->user->email;
		
		// set action into log
		$this->logger->addInfo('Payment with ID: '.$payment->id.' is set to "paid"');
		
		// send mail to user
		$message = $this->renderer->fetch('emails/payment-successful.twig', ['eventName' => $this->eventName]);
		$subject = $this->eventName.' - platba úspěšně přijata!';
		$this->mailer->sendMail($userEmail, $subject, $message);
	}
}