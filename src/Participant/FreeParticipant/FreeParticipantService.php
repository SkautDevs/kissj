<?php

namespace kissj\Participant\FreeParticipant;

use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserService;

class FreeParticipantService {
    private $freeParticipantRepository;
    private $paymentRepository;
    private $userService;
    private $paymentService;
    private $flashMessages;
    private $mailer;

    public function __construct(
        FreeParticipantRepository $freeParticipantRepository,
        PaymentRepository $paymentRepository,
        UserService $userService,
        PaymentService $paymentService,
        FlashMessagesBySession $flashMessages,
        PhpMailerWrapper $mailer
    ) {
        $this->freeParticipantRepository = $freeParticipantRepository;
        $this->paymentRepository = $paymentRepository;
        $this->userService = $userService;
        $this->paymentService = $paymentService;
        $this->flashMessages = $flashMessages;
        $this->mailer = $mailer;
    }

    public function getFreeParticipant(User $user): FreeParticipant {
        if ($this->freeParticipantRepository->countBy(['user' => $user]) === 0) {
            $freeParticipant = new FreeParticipant();
            $freeParticipant->user = $user;
            $this->freeParticipantRepository->persist($freeParticipant);
        }

        return $this->freeParticipantRepository->findOneBy(['user' => $user]);
    }

    public function addParamsIntoFreeParticipant(FreeParticipant $freeP, array $params): FreeParticipant {
        $freeP->firstName = $params['firstName'] ?? null;
        $freeP->lastName = $params['lastName'] ?? null;
        $freeP->nickname = $params['nickname'] ?? null;
        if ($params['birthDate'] !== null) {
            $freeP->birthDate = new \DateTime($params['birthDate']);
        }
        $freeP->gender = $params['gender'] ?? null;
        $freeP->email = $params['email'] ?? null;
        $freeP->telephoneNumber = $params['telephoneNumber'] ?? null;
        $freeP->permanentResidence = $params['permanentResidence'] ?? null;
        $freeP->country = $params['country'] ?? null;
        $freeP->scoutUnit = $params['scoutUnit'] ?? null;
        $freeP->setTshirt($params['tshirtShape'] ?? null, $params['tshirtSize'] ?? null);
        $freeP->foodPreferences = $params['foodPreferences'] ?? null;
        $freeP->healthProblems = $params['healthProblems'] ?? null;
        $freeP->languages = $params['languages'] ?? null;
        $freeP->swimming = $params['swimming'] ?? null;
        $freeP->notes = $params['notes'] ?? null;
        $freeP->legalRepresentative = $params['legalRepresentative'] ?? null;

        return $freeP;
    }

    public function isFreeParticipantValidForClose(FreeParticipant $freeP): bool {
        if (
            $freeP->firstName === null
            || $freeP->lastName === null
            || $freeP->birthDate === null
            || $freeP->gender === null
            || $freeP->email === null
            || $freeP->telephoneNumber === null
            || $freeP->permanentResidence === null
            || $freeP->country === null
            || $freeP->scoutUnit === null
            || $freeP->foodPreferences === null
            || $freeP->swimming === null
            || $freeP->legalRepresentative === null
        ) {
            return false;
        }

        if (!empty($freeP->email) && filter_var($freeP->email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        return true;
    }

    public function isCloseRegistrationValid(FreeParticipant $freeParticipant): bool {
        if (!$this->isFreeParticipantValidForClose($freeParticipant)) {
            $this->flashMessages->warning('Cannot lock the registration - some details are wrong or missing (probably email or some date)');

            return false;
        }
        if ($this->userService->getClosedFreeParticipantsCount() >= $freeParticipant->user->event->maximalClosedFreeParticipantsCount) {
            $this->flashMessages->warning('For free participants we have full registration now and you are below the bar, so we cannot register you yet. Please wait for limit rise');

            return false;
        }

        return true;
    }

    public function closeRegistration(FreeParticipant $ifreeParticipantt): FreeParticipant {
        if ($this->isCloseRegistrationValid($ifreeParticipantt)) {
            $this->userService->closeRegistration($ifreeParticipantt->user);
            $this->mailer->sendRegistrationClosed($ifreeParticipantt->user);
        }

        return $ifreeParticipantt;
    }

    public function getAllFreeParticipantStatistics(): StatisticValueObject {
        $freeParticipants = $this->freeParticipantRepository->findAll();

        return new StatisticValueObject($freeParticipants);
    }

    public function openRegistration(FreeParticipant $freeParticipant, $reason): FreeParticipant {
        $this->mailer->sendDeniedRegistration($freeParticipant, $reason);
        $this->userService->openRegistration($freeParticipant->user);

        return $freeParticipant;
    }

    public function approveRegistration(FreeParticipant $freeParticipant): FreeParticipant {
        $price = $this->paymentService->getPrice($freeParticipant);
        $payment = $this->paymentRepository->createAndPersistNewPayment($freeParticipant, $price);

        $this->mailer->sendRegistrationApprovedWithPayment($freeParticipant, $payment);
        $this->userService->approveRegistration($freeParticipant->user);

        return $freeParticipant;
    }

    public function sendWelcome(FreeParticipant $freeParticipant): void {
        $this->mailer->sendWelcomeFreeParticipantMessage($freeParticipant);
    }
}
