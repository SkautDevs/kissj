<?php

namespace kissj\Participant\Ist;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Mailer\MailerInterface;
use kissj\Orm\Relation;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\User\User;
use Slim\Views\Twig;

class IstService {
    private $paymentRepository;
    private $roleService;
    private $flashMessages;
    private $renderer;
    private $mailer;
    private $eventSettings;

    public function __construct(
        PaymentRepository $paymentRepository,
        FlashMessagesInterface $flashMessages,
        MailerInterface $mailer,
        Twig $renderer,
        $eventSettings,
        $eventName
    ) {
        $this->istRepository = $istRepository;
        $this->roleRepository = $roleRepository;
        $this->paymentRepository = $paymentRepository;
        $this->roleService = $userStatusService;
        $this->flashMessages = $flashMessages;
        $this->mailer = $mailer;
        $this->renderer = $renderer;
        $this->eventSettings = $eventSettings;
        $this->eventName = $eventName;
    }

    public function getIst(User $user): Ist {
        if ($this->istRepository->countBy(['user' => $user]) === 0) {
            $ist = new Ist();
            $ist->user = $user;
            $this->istRepository->persist($ist);
        }

        $ist = $this->istRepository->findOneBy(['user' => $user]);

        return $ist;
    }

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

    private function isIstValid(Ist $ist): bool {
        return $this->isIstDetailsValid(
            $ist->firstName,
            $ist->lastName,
            $ist->nickname,
            ($ist->birthDate ? $ist->birthDate->format('Y-m-d') : null),
            $ist->gender,
            $ist->permanentResidence,
            $ist->email,
            $ist->legalRepresestative,
            $ist->scarf,
            $ist->notes);
    }

    public function isIstDetailsValid(
        ?string $firstName,
        ?string $lastName,
        ?string $nickname,
        ?string $birthDate,
        ?string $gender,
        ?string $permanentResidence,
        ?string $email,
        ?string $legalRepresestative,
        ?string $scarf,
        ?string $notes
    ): bool {
        $validFlag = true;


        if (is_null($firstName) || is_null($lastName) || is_null($birthDate) || is_null($gender) || is_null($permanentResidence) || is_null($email) || is_null($scarf)) {
            $validFlag = false;
        }

        foreach ([$birthDate] as $date) {
            if (!empty($date) && $date !== date('Y-m-d', strtotime($date))) {
                $validFlag = false;
                break;
            }
        }

        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $validFlag = false;
        }

        return $validFlag;
    }

    public function editIstInfo(
        Ist $ist,
        ?string $firstName,
        ?string $lastName,
        ?string $nickname,
        ?string $birthDate,
        ?string $gender,
        ?string $permanentResidence,
        ?string $email,
        ?string $legalRepresestative,
        ?string $scarf,
        ?string $notes
    ) {
        $ist->firstName = $firstName;
        $ist->lastName = $lastName;
        $ist->nickname = $nickname;
        $ist->birthDate = new \DateTime($birthDate);
        $ist->gender = $gender;
        $ist->permanentResidence = $permanentResidence;
        $ist->email = $email;
        $ist->legalRepresestative = $legalRepresestative;
        $ist->scarf = $scarf;
        $ist->notes = $notes;

        $this->istRepository->persist($ist);
    }

    public function isCloseRegistrationValid(Ist $ist): bool {
        $validityFlag = true;
        if (!$this->isIstValid($ist)) {
            $this->flashMessages->warning('Nelze uzavřít registraci - údaje nejsou kompletní');
            $validityFlag = false;
        }
        if ($this->getClosedIstsCount() >= $this->eventSettings['maximalClosedIstsCount']) {
            $this->flashMessages->warning('Registraci už má uzavřenou maximální počet možných účastníků a ty se nevejdeš do počtu. Počkej prosím na zvýšení limitu.');
            $validityFlag = false;
        }

        return $validityFlag;
    }

    private function getClosedIstsCount(): int {
        return $this->roleRepository->countBy([
            'name' => 'ist',
            'event' => $this->eventName,
            'status' => new Relation('open', '!='),
        ]);
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

    // TODO clean up this four functions
    public function closeRegistration(Ist $ist) {
        /** @var Role $role */
        $role = $this->roleRepository->findOneBy(['userId' => $ist->user->id]);
        $role->status = $this->roleService->getCloseStatus();
        $this->roleRepository->persist($role);
    }

    public function approveIst(Ist $ist) {
        /** @var Role $role */
        $role = $this->roleRepository->findOneBy(['userId' => $ist->user->id]);
        $role->status = $this->roleService->getApproveStatus();
        $this->roleRepository->persist($role);
    }

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
