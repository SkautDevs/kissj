<?php

namespace kissj\Payment;

use h4kuna\Fio\FioRead;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Mailer\MailerInterface;
use kissj\Participant\Participant;
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
    /** @var FlashMessagesInterface $flashMessages */
    private $flashMessages;
    /** @var Logger $logger */
    private $logger;

    public function __construct(
        array $paymentsSettings,
        PaymentRepository $paymentRepository,
        FioRead $paymentAutoMatcherFio,
        Twig $renderer,
        FlashMessagesInterface $flashMessages,
        Logger $logger,
        string $eventName,
        MailerInterface $mailer,
        Random $random
    ) {
        $this->settings = $paymentsSettings;
        $this->paymentRepository = $paymentRepository;
        $this->paymentAutoMatcherFio = $paymentAutoMatcherFio;
        $this->mailer = $mailer;
        $this->renderer = $renderer;
        $this->flashMessages = $flashMessages;
        $this->logger = $logger;
        $this->eventName = $eventName;
        $this->random = $random;
    }

    public function findLastPayment(Participant $participant): ?Payment {
        return $this->paymentRepository->findOneBy(
            ['event' => $participant->user->event, 'user' => $participant->user],
            ['created_at' => 'DESC']
        );
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
            'price' => $price,
        ]));
    }

    # Jak vygenerovat hezci CSV z Money S3
    /* cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;0" | head -n1 > test.csv; cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;1" >> test.csv */

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

    public function pairNewPayments(array $approvedIstPayments) {
        $canceledPayments = $this->getCanceledPayments('korbo2019');
        // get list of new payments from bank
        $transactionsList = $this->paymentAutoMatcherFio->lastDownload();

        $counterSetPaid = 0;
        $counterUnknownPayment = 0;
        $counterWasPaid = 0;
        // iterate and try find a match
        /** @var $transaction \h4kuna\Fio\Response\Read\Transaction */
        foreach ($transactionsList as $transaction) {
            $paidFlag = false;
            foreach ($approvedIstPayments as $payment) {
                /** @var Payment $payment */
                $payment = $payment['payment'];
                if ($payment->variableSymbol == $transaction->variableSymbol && $payment->price == $transaction->volume) {
                    // match!
                    if ($payment->status == 'waiting') {
                        // not canceler or paid already
                        $this->setPaymentPaid($payment);
                        // TODO find a better place - all other logging is in controllers now
                        $this->logger->addInfo('Payment '.$payment->id.' is set to '.$payment->status.' automatically');
                        $counterSetPaid++;
                    } elseif ($payment->status == 'paid') {
                        // because of re-check from bank
                        $counterWasPaid++;
                    }
                    $paidFlag = true;
                    break;
                }
            }
            // nonrecognized transaction
            if ($paidFlag === false) {
                $counterUnknownPayment++;

                $canceledFlag = false;
                /** @var Payment $canceledPayment */
                foreach ($canceledPayments as $canceledPayment) {
                    if ($canceledPayment->variableSymbol == $transaction->variableSymbol && $canceledPayment->price == $transaction->volume) {
                        // TODO better system for this warning
                        $this->flashMessages->error(htmlspecialchars(
                            'Zaplacená zrušená platba: '.$transaction->volume.
                            ' Kč, VS: '.($transaction->variableSymbol).
                            ', zaplatil účastník registrovaný mailem: '.$canceledPayment->role->user->email,
                            ENT_QUOTES));

                        $canceledFlag = true;
                        break;
                    }
                }

                if ($canceledFlag === false) {
                    // TODO better system for this warning
                    $this->flashMessages->warning(htmlspecialchars(
                        'Nerozeznaná platba: '.$transaction->volume.
                        ' Kč, VS: '.($transaction->variableSymbol ?? 'není').
                        ', od: '.($transaction->nameAccountTo ?? 'plátce neznámý').
                        ', poznámka: '.($transaction->messageTo ?? 'není'),
                        ENT_QUOTES));
                }
            }
        }

        // TODO better system for outputting these
        if ($counterSetPaid) {
            $this->flashMessages->success('Spárováno '.$counterSetPaid.' plateb s transakcemi z banky!');
        }

        if ($counterUnknownPayment) {
            $this->flashMessages->info('Nerozeznáno celkem '.$counterUnknownPayment.' bankovních transakcí.');
        }

        $counterUnpayedPayments = count($approvedIstPayments) - $counterWasPaid - $counterSetPaid;
        if ($counterUnpayedPayments) {
            $this->flashMessages->info('Zbývá zaplatit celkem '.$counterUnpayedPayments.' plateb.');
        } else {
            $this->flashMessages->success('Na zaplacení nezbývají žádné platby!');
        }
    }

    public function setLastDate(string $date): void {
        $this->paymentAutoMatcherFio->setLastDate('2017-01-01');
    }

    /**
     * @param string $event
     * @return Payment[]
     */
    private function getCanceledPayments(string $event): array {
        return $this->paymentRepository->findBy(['event' => $event, 'status' => 'canceled']);
    }

    public function cancelPayment(Payment $payment): void {
        if ($payment->status != 'waiting') {
            throw new \Exception('Payment cancelation is allow only for payments with status "waiting"');
        }

        $payment->status = 'canceled';
        $this->paymentRepository->persist($payment);
    }

    public function sendCancelPaymentMail(Role $role, string $reason): void {
        $message = $this->renderer->fetch('emails/cancel-payment.twig', ['reason' => $reason]);
        $subject = 'Registrace Korbo 2019 - zrušení platby!';
        $this->mailer->sendMailFromTemplate($role->user->email, $subject, $message);
    }

    private function sendSuccesfulPaymentEmail(Role $role) {
        // send mail to user
        $message = $this->renderer->fetch('emails/payment-successful.twig', []);
        $subject = 'Registrace Korbo 2019 - platba úspěšně přijata!';
        $this->mailer->sendMailFromTemplate($role->user->email, $subject, $message);
    }
}
