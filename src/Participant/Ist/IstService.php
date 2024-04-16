<?php

declare(strict_types=1);

namespace kissj\Participant\Ist;
use DateTimeImmutable;
use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentStatus;
use kissj\User\User;


readonly class IstService
{

    public function __construct(
        private ParticipantRepository $participantRepository,
        private PaymentRepository $paymentRepository,
    ) {
    }

    /**
     * @param array<string> $preferredPosition
     */
    public function  createIstPayment (
        User $user,
        Event $event,
        string $contingent,
        string $firstName,
        string $lastName,
        string $nickname,
        string $permanentResidence,
        string $telephoneNumber,
        string $email,
        string $scoutUnit,
        DateTimeImmutable $birthDate,
        string $healthProblems,
        string $medicaments,
        string $psychicalHealthProblems,
        string $foodPreferences,
        DateTimeImmutable $arrivalDate,
        string $skills,
        array $preferredPosition,
        bool $printedHandbook,
        string $notes,
        DateTimeImmutable $registrationCloseDate,
        DateTimeImmutable $registrationApproveDate,
        ?DateTimeImmutable $registrationPayDate,
        string $variableSymbol,
        int $price,
        PaymentStatus $paymentStatus,
        string $accountNumber,
        string $iban,
        string $swift,
        DateTimeImmutable $due,
    ): Participant {
        $participant = new Participant();
        $participant->user = $user;
        $participant->role = ParticipantRole::Ist;
        $participant->contingent = $contingent;
        $participant->firstName = $firstName;
        $participant->lastName = $lastName;
        $participant->nickname = $nickname;
        $participant->permanentResidence = $permanentResidence;
        $participant->telephoneNumber = $telephoneNumber;
        $participant->email = $email;
        $participant->scoutUnit = $scoutUnit;
        $participant->birthDate = $birthDate;
        $participant->healthProblems = $healthProblems;
        $participant->medicaments = $medicaments;
        $participant->psychicalHealthProblems = $psychicalHealthProblems;
        $participant->foodPreferences = $foodPreferences;
        $participant->arrivalDate = $arrivalDate;
        $participant->skills = $skills;
        $participant->preferredPosition = $preferredPosition;
        $participant->printedHandbook = $printedHandbook;
        $participant->notes = $notes;
        $participant->registrationCloseDate = $registrationCloseDate;
        $participant->registrationApproveDate = $registrationApproveDate;
        $participant->registrationPayDate = $registrationPayDate;

        $this->participantRepository->persist($participant);

        $payment = new Payment();
        $payment->variableSymbol = $variableSymbol;
        $payment->price = (string)$price;
        $payment->currency = 'Kč';
        $payment->status = $paymentStatus;
        $payment->purpose = 'fee';
        $payment->accountNumber = $accountNumber;
        $payment->iban = $iban;
        $payment->swift = $swift;
        $payment->due = $due;
        $payment->note = $event->slug . ' ' . $participant->getFullName();
        $payment->participant = $participant;

        $this->paymentRepository->persist($payment);

        return $participant;
    }
}