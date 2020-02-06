<?php

namespace kissj\Payment;

use kissj\FlashMessages\FlashMessagesBySession;
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
        Logger $logger
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
            return 60;
        }

        throw new \RuntimeException('Generating price for unknown role - participant ID: '.$participant->id);
    }

    private function getFullPriceForToday(): int {
        $lastDiscountDay = new \DateTime('2020-03-15');

        if (new \DateTime('now') <= $lastDiscountDay) {
            return 150;
        }

        return 160;
    }

    public function cancelPayment(Payment $payment): Payment {
        if ($payment->status !== Payment::STATUS_WAITING) {
            throw new \RuntimeException('Payment cancelation is allow only for payments with status "'
                .Payment::STATUS_WAITING.'"');
        }

        $payment->status = Payment::STATUS_CANCELED;
        $this->paymentRepository->persist($payment);

        return $payment;
    }

    public function confirmPayment(Payment $payment): Payment {
        if ($payment->status !== Payment::STATUS_WAITING) {
            throw new \RuntimeException('Payment confirmation is allow only for payments with status "'
                .Payment::STATUS_WAITING.'"');
        }

        $payment->status = Payment::STATUS_PAID;
        $this->paymentRepository->persist($payment);

        return $payment;
    }

    // TODO clean

    # Jak vygenerovat hezci CSV z Money S3
    /* cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;0" | head -n1 > test.csv; cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;1" >> test.csv */

    public function pairNewPayments(array $approvedIstPayments) {
        /** @var Payment[] $canceledPayments */
        $canceledPayments = $this->paymentRepository->findBy(['event' => 'korbo2019', 'status' => 'canceled']);
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
                        $this->sendSuccesfulPaymentEmail($payment);
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

    public function findLastPayment(Participant $participant): ?Payment {
        // TODO refactor
        $payment = reset($participant->payment);

        if ($payment === false) {
            return null;
        }

        return $payment;
    }
}
