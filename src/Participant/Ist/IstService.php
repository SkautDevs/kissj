<?php

namespace kissj\Participant\Ist;

use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Admin\StatisticValueObject;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserService;
use Slim\Psr7\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class IstService {
    private $istRepository;
    private $userService;
    private $paymentService;
    private $flashMessages;
    private $translator;
    private $mailer;

    public function __construct(
        IstRepository $istRepository,
        UserService $userService,
        PaymentService $paymentService,
        FlashMessagesBySession $flashMessages,
        TranslatorInterface $translator,
        PhpMailerWrapper $mailer
    ) {
        $this->istRepository = $istRepository;
        $this->userService = $userService;
        $this->paymentService = $paymentService;
        $this->flashMessages = $flashMessages;
        $this->translator = $translator;
        $this->mailer = $mailer;
    }

    public function getIst(User $user): Ist {
        if ($this->istRepository->countBy(['user' => $user]) === 0) {
            $ist = new Ist();
            $ist->user = $user;
            $this->istRepository->persist($ist);
        }

        return $this->istRepository->findOneBy(['user' => $user]);
    }

    public function handleUploadedFile(Ist $ist, UploadedFile $uploadedFile): Ist {
        // check for too-big files
        if ($uploadedFile->getSize() > 10000000) { // 10MB
            $this->flashMessages->warning($this->translator->trans('flash.warning.fileTooBig'));

            return $ist;
        }

        $uploadDirectory = __DIR__.'/../../../uploads/';
        $newFilename = \md5(microtime(true));
        $uploadedFile->moveTo($uploadDirectory.DIRECTORY_SEPARATOR.$newFilename);

        $ist->uploadedFilename = $newFilename;
        $ist->uploadedOriginalFilename = $uploadedFile->getClientFilename();
        $ist->uploadedContenttype = $uploadedFile->getClientMediaType();
        $this->istRepository->persist($ist);

        return $ist;
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
        $ist->scarf = $params['scarf'] ?? null;
        $ist->notes = $params['notes'] ?? null;

        return $ist;
    }

    public function isIstValidForClose(Ist $ist): bool {
        if (
            $ist->firstName === null
            || $ist->lastName === null
            || $ist->birthDate === null
            || $ist->gender === null
            //|| $ist->email === null
            //|| $ist->telephoneNumber === null
            || $ist->permanentResidence === null
            || $ist->country === null
            || $ist->scoutUnit === null
            //|| $ist->foodPreferences === null
            //|| $ist->languages === null
            //|| $ist->swimming === null
            //|| $ist->driversLicense === null
            //|| $ist->preferredPosition === null
            //|| $ist->getTshirtShape() === null
            //|| $ist->getTshirtSize() === null
        ) {
            return false;
        }
        /*
                if (!empty($ist->email) && filter_var($ist->email, FILTER_VALIDATE_EMAIL) === false) {
                    return false;
                }
        */
        return true;
    }

    public function isCloseRegistrationValid(Ist $ist): bool {
        if (!$this->isIstValidForClose($ist)) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.istNoLock'));

            return false;
        }
        if ($this->userService->getClosedIstsCount() >= $ist->user->event->maximalClosedIstsCount) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.istFullRegistration'));

            return false;
        }

        return true;
    }

    public function closeRegistration(Ist $ist): Ist {
        if ($this->isCloseRegistrationValid($ist)) {
            $this->userService->closeRegistration($ist->user);
            $this->mailer->sendRegistrationClosed($ist->user);
        }

        return $ist;
    }

    public function getAllIstsStatistics(): StatisticValueObject {
        $ists = $this->istRepository->findAll();

        return new StatisticValueObject($ists);
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
}
