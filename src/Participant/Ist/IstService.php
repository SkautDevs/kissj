<?php

namespace kissj\Participant\Ist;

use kissj\AbstractService;
use kissj\Event\Event;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Admin\AdminService;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserService;
use Symfony\Contracts\Translation\TranslatorInterface;

class IstService extends AbstractService
{
    public function __construct(
        private IstRepository $istRepository,
        private UserService $userService,
        private PaymentService $paymentService,
        private AdminService $adminService,
        private FlashMessagesBySession $flashMessages,
        private TranslatorInterface $translator,
        private PhpMailerWrapper $mailer,
    ) {
    }

    public function getIst(User $user): Ist
    {
        if ($this->istRepository->countBy(['user' => $user]) === 0) {
            $ist = new Ist();
            $ist->user = $user;
            $this->istRepository->persist($ist);
        }

        return $this->istRepository->findOneBy(['user' => $user]);
    }

    public function addParamsIntoIst(Ist $ist, array $params): Ist
    {
        $this->addParamsIntoPerson($params, $ist);
        $ist->driversLicense = $params['driversLicense'] ?? null;
        $ist->skills = $params['skills'] ?? null;
        $ist->preferredPosition = $params['preferredPosition'] ?? [];

        return $ist;
    }

    public function isIstValidForClose(Ist $ist): bool
    {
        $ca = $ist->user->event->eventType->getContentArbiterIst();
        if (
            ($ca->skills && $ist->skills === null)
            || ($ca->preferredPosition && $ist->preferredPosition === null)
            || ($ca->driver && $ist->driversLicense === null)
        ) {
            return false;
        }

        return $this->isPersonValidForClose($ist, $ca);
    }

    public function isCloseRegistrationValid(Ist $ist): bool
    {
        $validityFlag = true;
        if (!$this->isIstValidForClose($ist)) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.istNoLock'));

            $validityFlag = false;
        }

        $event = $ist->user->event;
        if (
            $this->userService->getClosedIstsCount($event) 
            >= $event->getEventType()->getMaximumClosedParticipants($ist)
        ) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.istFullRegistration'));

            $validityFlag = false;
        }
        
        if (!$event->getEventType()->isLockRegistrationAllowed()) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.registrationNotAllowed'));

            $validityFlag = false;
        }

        // to show all warnings
        return $validityFlag;
    }

    public function closeRegistration(Ist $ist): Ist
    {
        if ($this->isCloseRegistrationValid($ist)) {
            $this->userService->closeRegistration($ist->user);
            $this->mailer->sendRegistrationClosed($ist->user);
        }

        return $ist;
    }

    public function approveRegistration(Ist $ist): Ist
    {
        $payment = $this->paymentService->createAndPersistNewPayment($ist);

        $this->mailer->sendRegistrationApprovedWithPayment($ist, $payment);
        $this->userService->approveRegistration($ist->user);

        return $ist;
    }

    public function getAllIstsStatistics(Event $event, User $admin): StatisticValueObject
    {
        $ists = $this->adminService->filterContingentAdminParticipants(
            $admin,
            $this->istRepository->findAllWithEvent($event)
        );

        return new StatisticValueObject($ists);
    }
}
