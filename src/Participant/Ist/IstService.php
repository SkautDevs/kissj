<?php

namespace kissj\Participant\Ist;

use kissj\AbstractService;
use kissj\Event\ContentArbiterIst;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserService;
use Symfony\Contracts\Translation\TranslatorInterface;

class IstService extends AbstractService {
    private IstRepository $istRepository;
    private UserService $userService;
    private PaymentService $paymentService;
    private FlashMessagesBySession $flashMessages;
    private TranslatorInterface $translator;
    private PhpMailerWrapper $mailer;
    private ContentArbiterIst $contentArbiter;

    public function __construct(
        IstRepository $istRepository,
        UserService $userService,
        PaymentService $paymentService,
        FlashMessagesBySession $flashMessages,
        TranslatorInterface $translator,
        PhpMailerWrapper $mailer,
        ContentArbiterIst $contentArbiter
    ) {
        $this->istRepository = $istRepository;
        $this->userService = $userService;
        $this->paymentService = $paymentService;
        $this->flashMessages = $flashMessages;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->contentArbiter = $contentArbiter;
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
        $this->addParamsIntoPerson($params['notes'], $ist);
        $ist->driversLicense = $params['driversLicense'] ?? null;
        $ist->skills = $params['skills'] ?? null;
        $ist->preferredPosition = $params['preferredPosition'] ?? [];

        return $ist;
    }

    public function isIstValidForClose(Ist $ist): bool {
        if (
            ($this->contentArbiter->skills && $ist->skills === null)
            || ($this->contentArbiter->preferredPosition && $ist->preferredPosition === null)
            || ($this->contentArbiter->driver && $ist->driversLicense === null)
        ) {
            return false;
        }

        return $this->isPersonValidForClose($ist, $this->contentArbiter);
    }

    public function isCloseRegistrationValid(Ist $ist): bool {
        $validityFlag = true;
        if (!$this->isIstValidForClose($ist)) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.istNoLock'));

            $validityFlag = false;
        }

        if ($this->userService->getClosedIstsCount() >= $ist->user->event->maximalClosedIstsCount) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.istFullRegistration'));

            $validityFlag = false;
        }

        // to show all warnings
        return $validityFlag;
    }

    public function closeRegistration(Ist $ist): Ist {
        if ($this->isCloseRegistrationValid($ist)) {
            $this->userService->closeRegistration($ist->user);
            $this->mailer->sendRegistrationClosed($ist->user);
        }

        return $ist;
    }

    public function openRegistration(Ist $ist, $reason): Ist {
        $this->mailer->sendDeniedRegistration($ist, $reason);
        $this->userService->openRegistration($ist->user);

        return $ist;
    }

    public function approveRegistration(Ist $ist): Ist {
        $payment = $this->paymentService->createAndPersistNewPayment($ist);

        $this->mailer->sendRegistrationApprovedWithPayment($ist, $payment);
        $this->userService->approveRegistration($ist->user);

        return $ist;
    }

    public function getAllIstsStatistics(): StatisticValueObject {
        $ists = $this->istRepository->findAll();

        return new StatisticValueObject($ists);
    }
}
