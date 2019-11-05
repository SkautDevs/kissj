<?php

namespace kissj\Participant\Ist;

use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\MailerInterface;
use kissj\Orm\Relation;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\User\User;
use kissj\User\UserService;

class IstService {
    private $istRepository;
    private $paymentRepository;
    private $flashMessages;
    private $mailer;
    private $userService;

    public function __construct(
        IstRepository $istRepository,
        PaymentRepository $paymentRepository,
        FlashMessagesBySession $flashMessages,
        MailerInterface $mailer,
        UserService $userService
    ) {
        $this->istRepository = $istRepository;
        $this->paymentRepository = $paymentRepository;
        $this->flashMessages = $flashMessages;
        $this->mailer = $mailer;
        $this->userService = $userService;
    }

    public function getIst(User $user): Ist {
        if ($this->istRepository->countBy(['user' => $user]) === 0) {
            $ist = new Ist();
            $ist->user = $user;
            $this->istRepository->persist($ist);
        }

        return $this->istRepository->findOneBy(['user' => $user]);
    }

    public function addParamsIntoIst(Ist $ist, array $params): Ist {
        $ist->firstName = $params['firstName'] ?? null;
        $ist->lastName = $params['lastName'] ?? null;
        $ist->nickname = $params['nickname'] ?? null;
        if ($params['birthDate'] !== null) {
            $ist->birthDate = new \DateTime($params['birthDate']);
        }
        $ist->gender = $params['gender'] ?? null;
        $ist->email = $params['email'] ?? null;
        $ist->telephoneNumber = $params['telephoneNumber'] ?? null;
        $ist->permanentResidence = $params['permanentResidence'] ?? null;
        $ist->country = $params['country'] ?? null;
        $ist->scoutUnit = $params['scoutUnit'] ?? null;
        $ist->setTshirt($params['tshirtShape'] ?? null, $params['tshirtSize'] ?? null);
        $ist->foodPreferences = $params['foodPreferences'] ?? null;
        $ist->healthProblems = $params['healthProblems'] ?? null;
        $ist->languages = $params['languages'] ?? null;
        $ist->swimming = $params['swimming'] ?? null;
        $ist->driversLicense = $params['driversLicense'] ?? null;
        $ist->skills = $params['skills'] ?? null;
        $ist->preferredPosition = $params['preferredPosition'] ?? [];
        $ist->notes = $params['notes'] ?? null;

        return $ist;
    }

    public function isIstValidForClose(Ist $ist): bool {
        if (
            $ist->firstName === null
            || $ist->lastName === null
            || $ist->birthDate === null
            || $ist->gender === null
            || $ist->email === null
            || $ist->telephoneNumber === null
            || $ist->permanentResidence === null
            || $ist->country === null
            || $ist->scoutUnit === null
            || $ist->foodPreferences === null
            || $ist->languages === null
            || $ist->swimming === null
            || $ist->driversLicense === null
            || $ist->preferredPosition === null
            || $ist->getTshirtShape() === null
            || $ist->getTshirtSize() === null
        ) {
            return false;
        }

        if (!empty($ist->email) && filter_var($ist->email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        return true;
    }

    public function isCloseRegistrationValid(Ist $ist): bool {
        if (!$this->isIstValidForClose($ist)) {
            $this->flashMessages->warning('Cannot lock the registration - some details are wrong or missing (probably email or some date)');

            return false;
        }
        if ($this->userService->getClosedIstsCount() >= $ist->user->event->maximalClosedIstsCount) {
            $this->flashMessages->warning('For IST we have full registration now and you are below the bar, so we cannot register you yet. Please wait for limit rise');

            return false;
        }

        return true;
    }

    public function closeRegistration(Ist $ist): Ist {
        if ($this->isCloseRegistrationValid($ist)) {
            $this->userService->closeRegistration($ist->user);
            $this->mailer->sendMailFromTemplate($ist->user->email, 'closed registration', 'closed', []);
        }

        return $ist;
    }

    // TODO fix

    public function getAllClosedIsts(): array {
        $closedIsts = $this->roleRepository->findBy([
            'event' => $this->eventName,
            'name' => 'ist',
            'status' => 'closed',
        ]);
        $ists = [];
        /** @var Role $closedIst */
        foreach ($closedIsts as $closedIst) {
            $ists[] = $this->istRepository->findOneBy(['userId' => $closedIst->user->id]);
        }

        return $ists;
    }

    public function getAllApprovedIstsWithPayment(): array {
        $approvedIsts = $this->roleRepository->findBy([
            'event' => $this->eventName,
            'name' => 'ist',
            'status' => 'approved',
        ]);
        $ists = [];
        /** @var Role $approvedIst */
        foreach ($approvedIsts as $approvedIst) {
            $ist['info'] = $this->istRepository->findOneBy(['user' => $approvedIst->user]);
            $ist['payment'] = $this->getOneValidPayment($ist['info']);
            // TODO discuss moving this piece of logic elsewhere
            $ist['elapsedPaymentDays'] = $ist['payment']->generatedDate->diff(new \DateTime());
            $ist['elapsedPaymentDays'] = $ist['elapsedPaymentDays']->days;
            $ists[] = $ist;
        }

        return $ists;
    }

    public function getAllIstsStatistics(): array {
        $ists['limit'] = $this->eventSettings['maximalClosedIstsCount'];
        $ists['closed'] = $this->roleRepository->countBy([
            'name' => 'ist',
            'event' => $this->eventName,
            'status' => new Relation('closed', '=='),
        ]);

        $ists['approved'] = $this->roleRepository->countBy([
            'name' => 'ist',
            'event' => $this->eventName,
            'status' => new Relation('approved', '=='),
        ]);

        $ists['paid'] = $this->roleRepository->countBy([
            'name' => 'ist',
            'event' => $this->eventName,
            'status' => new Relation('paid', '=='),
        ]);

        return $ists;
    }

    public function sendPaymentByMail(Payment $payment, Ist $ist) {
        $message = $this->renderer->fetch('emails/payment-info.twig', [
            'eventName' => 'Korbo 2019',
            'accountNumber' => $payment->accountNumber,
            'price' => $payment->price,
            'currency' => 'Kč',
            'variableSymbol' => $payment->variableSymbol,
            'role' => $payment->role->name,
            'gender' => $ist->gender,

            'istFullName' => $ist->firstName.' '.$ist->lastName,
        ]);

        $this->mailer->sendMailFromTemplate($payment->role->user->email, 'Registrace Korbo 2019 - platební informace',
            $message);
    }

    public function sendDenialMail(Ist $ist, string $reason) {
        $message = $this->renderer->fetch('emails/denial.twig', [
            'eventName' => 'Korbo 2019',
            'role' => 'ist',
            'reason' => $reason,
        ]);

        $this->mailer->sendMailFromTemplate($ist->user->email, 'Registrace Korbo 2019 - zamítnutí registrace',
            $message);
    }

    // TODO clear
    public function openIst(Ist $ist) {
        /** @var Role $role */
        $role = $this->roleRepository->findOneBy(['userId' => $ist->user->id]);
        $role->status = $this->roleService->getOpenStatus();
        $this->roleRepository->persist($role);
    }

    public function cancelApprovementIst(Ist $ist) {
        /** @var Role $role */
        $role = $this->roleRepository->findOneBy(['userId' => $ist->user->id]);
        $role->status = $this->roleService->getCloseStatus();
        $this->roleRepository->persist($role);
    }
}
