<?php

namespace kissj\Payment;

use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use Monolog\Logger;

class PaymentService {
    private $paymentRepository;
    private $paymentAutoMatcherFio;
    private $flashMessages;
    private $logger;

    public function __construct(
        PaymentRepository $paymentRepository,
        //FioRead $paymentAutoMatcherFio,
        FlashMessagesBySession $flashMessages,
        Logger $logger,
        PhpMailerWrapper $mailer
    ) {
        $this->paymentRepository = $paymentRepository;
        //$this->paymentAutoMatcherFio = $paymentAutoMatcherFio;
        $this->flashMessages = $flashMessages;
        $this->logger = $logger;
    }

    /**
     * Participants pays 150€ till 15/3/20, 160€ from 16/3/20, staff 50€
     * discount 40€ for self-eating participant
     *
     * @param Participant $participant
     * @return int
     */
    public function getPrice(Participant $participant): int {
        if ($participant instanceof PatrolLeader) {
            $todayPrice = $this->getFullPriceForToday();
            $patrolPriceSum = 0;
            $fullPatrol = array_merge([$participant], $participant->patrolParticipants);
            /** @var Participant $patrolParticipant */
            foreach ($fullPatrol as $patrolParticipant) {
                $patrolPriceSum += $todayPrice;
                if ($patrolParticipant->foodPreferences === Participant::FOOD_OTHER) {
                    $patrolPriceSum -= 40;
                }
            }

            return $patrolPriceSum;
        }

        if ($participant instanceof Ist) {
            return 50; // TODO check if other diet gets the discount
        }

        // TODO check what to do with guests

        throw new \RuntimeException('Generating price for unknown role - participant ID: '.$participant->id);
    }

    private function getFullPriceForToday(): int {
        $lastDiscountDay = new \DateTime('2020-03-15');

        if (new \DateTime('now') <= $lastDiscountDay) {
            return 150;
        }

        return 160;
    }

    // TODO clean

    public function findLastPayment(Participant $participant): ?Payment {
        $criteria = ['participant' => $participant];
        if ($this->paymentRepository->isExisting($criteria)) {
            return $this->paymentRepository->findOneBy(
                $criteria,
                ['created_at' => false]
            );
        }

        return null;
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
}
