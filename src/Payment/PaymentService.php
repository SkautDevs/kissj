<?php

namespace kissj\Payment;

use kissj\BankPayment\BankPayment;
use kissj\BankPayment\BankPaymentRepository;
use kissj\BankPayment\FioBankPaymentService;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\FreeParticipant\FreeParticipant;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use Monolog\Logger;
use Symfony\Contracts\Translation\TranslatorInterface;

class PaymentService {
    private $bankPaymentService;
    private $bankPaymentRepository;
    private $paymentRepository;
    private $flashMessages;
    private $mailer;
    private $translator;
    private $logger;

    public function __construct(
        FioBankPaymentService $bankPaymentService,
        PaymentRepository $paymentRepository,
        BankPaymentRepository $bankPaymentRepository,
        FlashMessagesBySession $flashMessages,
        PhpMailerWrapper $mailer,
        TranslatorInterface $translator,
        Logger $logger
    ) {
        $this->bankPaymentService = $bankPaymentService;
        $this->bankPaymentRepository = $bankPaymentRepository;
        $this->paymentRepository = $paymentRepository;
        $this->flashMessages = $flashMessages;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function createAndPersistNewPayment(Participant $participant): Payment {
        do {
            $prefix = $participant->user->event->prefixVariableSymbol;
            $variableNumber = $prefix.str_pad(random_int(0, 999999), 3, '0', STR_PAD_LEFT);
        } while ($this->paymentRepository->isVariableNumberExisting($variableNumber));

        $event = $participant->user->event;

        $payment = new Payment();
        $payment->participant = $participant;
        $payment->variableSymbol = $variableNumber;
        $payment->price = (string)$this->getPrice($participant);
        $payment->currency = 'Kč';
        $payment->status = Payment::STATUS_WAITING;
        $payment->purpose = 'event fee';
        $payment->accountNumber = $event->accountNumber;
        if ($participant instanceof Ist) {
            $payment->note = $event->slug.' '
                .$participant->getFullName();
        }

        $this->paymentRepository->persist($payment);

        return $payment;
    }

    /**
     * @param Participant $participant
     * @return int
     */
    public function getPrice(Participant $participant): int {
        $price = 500;
        if ($participant->scarf === Participant::SCARF_YES) {
            $price += $participant->user->event->scarfPrice;
        }

        return $price;

        // TODO make dynamic for other events
        /*
         * AQUA2020
         * Participants pays 150€ till 15/3/20, 160€ from 16/3/20, staff 50€
         * discount 40€ for self-eating participant (free included), not for ISTs (not computed)
         */
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

        if ($participant instanceof FreeParticipant) {
            $price = $this->getFullPriceForToday();
            if ($participant->foodPreferences === Participant::FOOD_OTHER) {
                $price -= 40;
            }

            return $price;
        }

        throw new \RuntimeException('Generating price for unknown role - participant ID: '.$participant->id);
    }

    private function getFullPriceForToday(): int {
        $lastDiscountDay = new \DateTime('2020-03-20');

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

    public function findLastPayment(Participant $participant): ?Payment {
        // TODO refactor
        $payments = $participant->payment;

        if (count($payments) > 0) {
            return $payments[0];
        }
        return null;
    }

    /**
     * plan - frstly it looks, if they are any payments downloaded from bank to pair with our generated payments
     * if not, download fresh data from bank and then vvv
     * pair few of them (few because of mailing and processing time)
     *
     * @param int $limit
     */
    public function updatePayments(int $limit): void {
        $freshBankPayments = $this->bankPaymentRepository->findBy(['status', BankPayment::STATUS_FRESH]);
        if (count($freshBankPayments) === 0) {
            $this->bankPaymentService->getAndSafeFreshPaymentsFromBank();
            $freshBankPayments = $this->bankPaymentRepository->findBy(['status', BankPayment::STATUS_FRESH]);
        }
        
        $participantKeydPayments = $this->paymentRepository->getWaitingPaymentsKeydByVariableSymbols();
        $counterNewPaid = 0;
        $counterUnknownPayment = 0;

        /** @var BankPayment $bankPayment */
        foreach (array_slice($freshBankPayments, 0, $limit) as $bankPayment) {
            if (array_key_exists($bankPayment->variableSymbol, $participantKeydPayments)) {
                $payment = $participantKeydPayments[$bankPayment->variableSymbol];
                if ($payment->price === $bankPayment->price) {
                    // match!
                    $this->confirmPayment($payment);
                    $this->mailer->sendRegistrationPaid($payment->participant);
                    $this->logger->addInfo('Payment '.$payment->id.' is set to '.$payment->status.' automatically');

                    $bankPayment->status = BankPayment::STATUS_PAIRED;
                    $counterNewPaid++;
                } else {
                    // matching VS, not matchnig price
                    $bankPayment->status = BankPayment::STATUS_UNKNOWN;
                    $counterUnknownPayment++;
                }
            } else {
                // found no payment of this VS
                $bankPayment->status = BankPayment::STATUS_UNKNOWN;
                $counterUnknownPayment++;
            }

            $this->bankPaymentRepository->persist($bankPayment);
        }

        $this->flashMessages->info(
            $this->translator->trans('flash.info.markedNew').': '.$counterNewPaid.', '
            .$this->translator->trans('flash.info.newUnknown').': '.$counterUnknownPayment
        );
    }

    # Jak vygenerovat hezci CSV z Money S3
    /* cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;0" | head -n1 > test.csv; cat Seznam\ bankovních\ dokladů_04122017_pok.csv | grep "^Detail 1;1" >> test.csv */

    // TODO delete

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
                    if ($payment->status == 'waiting') {/*
                        // not canceler or paid already
                        $this->setPaymentPaid($payment);
                        $this->sendSuccesfulPaymentEmail($payment);*/
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
                        // TODO better system for this warning + do tranlation
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
                    // TODO better system for this warning + translation
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
            $this->flashMessages->success($this->translator->trans('flash.success.adminPairedPayments').$counterSetPaid.'!');
        }

        if ($counterUnknownPayment) {
            $this->flashMessages->info($this->translator->trans('flash.info.adminPaymentsUnrecognized').$counterUnknownPayment);
        }

        $counterUnpayedPayments = count($approvedIstPayments) - $counterWasPaid - $counterSetPaid;
        if ($counterUnpayedPayments) {
            $this->flashMessages->info($this->translator->trans('flash.info.adminPaymentsWaiting').$counterUnpayedPayments);
        } else {
            $this->flashMessages->success($this->translator->trans('flash.success.adminNoWaitingPayments'));
        }
    }
}
