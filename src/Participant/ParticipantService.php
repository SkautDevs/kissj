<?php

declare(strict_types=1);

namespace kissj\Participant;

use DateTimeImmutable;
use kissj\Event\AbstractContentArbiter;
use kissj\FileHandler\FileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Guest\Guest;
use kissj\Payment\Payment;
use kissj\Payment\PaymentService;
use kissj\User\User;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParticipantService
{
    public function __construct(
        private ParticipantRepository $participantRepository,
        private PaymentService $paymentService,
        private UserService $userService,
        private FlashMessagesBySession $flashMessages,
        private TranslatorInterface $translator,
        private PhpMailerWrapper $mailer,
        private FileHandler $fileHandler,
    ) {
    }

    /**
     * @param Participant $participant
     * @param string[] $params
     * @return Participant
     */
    public function addParamsIntoParticipant(Participant $participant, array $params): Participant
    {
        $participant = $this->addParamsIntoPerson($params, $participant);
        $this->participantRepository->persist($participant);

        return $participant;
    }

    /**
     * @param array<string, string> $params
     * @param Participant $p
     * @return Participant
     * @throws \Exception
     */
    private function addParamsIntoPerson(array $params, Participant $p): Participant
    {
        $p->patrolName = $params['patrolName'] ?? null;
        $p->contingent = $params['contingent'] ?? null;
        $p->firstName = $params['firstName'] ?? null;
        $p->lastName = $params['lastName'] ?? null;
        $p->nickname = $params['nickname'] ?? null;
        $p->permanentResidence = $params['permanentResidence'] ?? null;
        $p->telephoneNumber = $params['telephoneNumber'] ?? null;
        $p->gender = $params['gender'] ?? null;
        $p->country = $params['country'] ?? null;
        $p->email = $params['email'] ?? null;
        $p->scoutUnit = $params['scoutUnit'] ?? null;
        $p->languages = $params['languages'] ?? null;
        if (array_key_exists('birthDate', $params) && $params['birthDate'] !== null) {
            $p->birthDate = new \DateTime($params['birthDate']);
        }
        $p->birthPlace = $params['birthPlace'] ?? null;
        $p->healthProblems = $params['healthProblems'] ?? null;
        $p->foodPreferences = $params['foodPreferences'] ?? null;
        $p->idNumber = $params['idNumber'] ?? null;
        $p->scarf = $params['scarf'] ?? null;
        $p->swimming = $params['swimming'] ?? null;
        $p->setTshirt($params['tshirtShape'] ?? null, $params['tshirtSize'] ?? null);
        if (array_key_exists('arrivalDate', $params) && $params['arrivalDate'] !== null) {
            $p->arrivalDate = new \DateTime($params['arrivalDate']);
        }
        if (array_key_exists('departureDate', $params) && $params['departureDate'] !== null) {
            $p->departureDate = new \DateTime($params['departureDate']);
        }
        $p->skills = $params['skills'] ?? null;
        /** @var string[] $preferredPosition */
        $preferredPosition = $params['preferredPosition'] ?? [];
        $p->preferredPosition = $preferredPosition;
        $p->driversLicense = $params['driversLicense'] ?? null;
        $p->notes = $params['notes'] ?? null;

        return $p;
    }

    public function handleUploadedFiles(Participant $participant, Request $request): void
    {
        $contentArbiter = $this->getContentArbiterForParticipant($participant);

        if ($contentArbiter->uploadFile) {
            $uploadedFile = $this->resolveUploadedFiles($request);
            if ($uploadedFile instanceof UploadedFile) {
                $this->fileHandler->saveFileTo($participant, $uploadedFile);
            }
        }
    }

    protected function resolveUploadedFiles(Request $request): ?UploadedFile
    {
        $uploadedFiles = $request->getUploadedFiles();
        if (!array_key_exists('uploadFile', $uploadedFiles) || !$uploadedFiles['uploadFile'] instanceof UploadedFile) {
            // problem - too big file -> not save anything, because always got nulls in request fields
            $this->flashMessages->warning($this->translator->trans('flash.warning.fileTooBig'));

            return null;
        }

        $errorNum = $uploadedFiles['uploadFile']->getError();

        switch ($errorNum) {
            case UPLOAD_ERR_OK:
                $uploadedFile = $uploadedFiles['uploadFile'];

                // check for too-big files
                if ($uploadedFile->getSize() > 10_000_000) { // 10MB
                    $this->flashMessages->warning($this->translator->trans('flash.warning.fileTooBig'));

                    return null;
                }

                return $uploadedFile;
            case UPLOAD_ERR_INI_SIZE:
                $this->flashMessages->warning($this->translator->trans('flash.warning.fileTooBig'));

                return null;
            default:
                return null;
        }
    }

    public function isCloseRegistrationValid(Participant $participant): bool
    {
        $validityFlag = true;

        $event = $participant->getUserButNotNull()->event;
        if (!$this->isParticipantValidForClose($participant, $this->getContentArbiterForParticipant($participant))) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.noLock'));

            $validityFlag = false;
        }

        if (
            $this->getClosedSameRoleParticipantsCount($participant)
            >= $event->eventType->getMaximumClosedParticipants($participant)
        ) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.fullRegistration'));

            $validityFlag = false;
        }

        if (
            $event->maximalClosedParticipantsCount !== null
            && $this->getClosedParticipantsCount($participant) >= $event->maximalClosedParticipantsCount
        ) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.fullRegistration'));

            $validityFlag = false;
        }

        if (!$event->canRegistrationBeLocked()) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.registrationNotAllowed'));

            $validityFlag = false;
        }

        // to show all warnings
        return $validityFlag;
    }

    public function isParticipantValidForClose(Participant $p, AbstractContentArbiter $ca): bool
    {
        if (
            ($ca->patrolName && $p->patrolName === null)
            || ($ca->contingent && $p->contingent === null)
            || ($ca->firstName && $p->firstName === null)
            || ($ca->lastName && $p->lastName === null)
            || ($ca->address && $p->permanentResidence === null)
            || ($ca->phone && $p->telephoneNumber === null)
            || ($ca->gender && $p->gender === null)
            || ($ca->country && $p->country === null)
            || ($ca->email && $p->email === null)
            || ($ca->unit && $p->scoutUnit === null)
            || ($ca->languages && $p->languages === null)
            || ($ca->birthDate && $p->birthDate === null)
            || ($ca->birthPlace && $p->birthPlace === null)
            || ($ca->food && $p->foodPreferences === null)
            || ($ca->idNumber && $p->idNumber === null)
            || ($ca->scarf && $p->scarf === null)
            || ($ca->swimming && $p->swimming === null)
            || ($ca->arrivalDate && $p->arrivalDate === null)
            || ($ca->departureDate && $p->departureDate === null)
            #|| ($ca->uploadFile && $p->uploadedFilename === null)
            || ($ca->skills && $p->skills === null)
            || ($ca->preferredPosition && $p->preferredPosition === null)
            || ($ca->driver && $p->driversLicense === null)
            || ($ca->tshirt && $p->getTshirtShape() === null)
            || ($ca->tshirt && $p->getTshirtSize() === null)
        ) {
            return false;
        }

        if ($ca->email && !empty($p->email) && filter_var($p->email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        // numbers and plus sight up front only
        if ($ca->phone && !empty($p->telephoneNumber) && preg_match('/^\+?[0-9 ]+$/', $p->telephoneNumber) === 0) {
            return false;
        }

        return true;
    }

    public function getClosedSameRoleParticipantsCount(Participant $participant): int
    {
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            [$participant->role ?? ''],
            [
                UserStatus::Closed,
                UserStatus::Approved,
                UserStatus::Paid,
            ],
            $participant->getUserButNotNull()->event,
        );

        return count($this->filterSameContingent($participants, $participant->contingent));
    }

    public function getClosedParticipantsCount(Participant $participant): int
    {
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            [
                User::ROLE_PATROL_LEADER,
                User::ROLE_PATROL_PARTICIPANT,
                User::ROLE_TROOP_LEADER,
                User::ROLE_TROOP_PARTICIPANT,
                User::ROLE_IST,
                User::ROLE_GUEST,
            ],
            [
                UserStatus::Closed,
                UserStatus::Approved,
                UserStatus::Paid,
            ],
            $participant->getUserButNotNull()->event,
        );

        return count($participants);
    }

    /**
     * @param Participant[] $participants
     * @param string|null $contingent
     * @return Participant[]
     */
    private function filterSameContingent(array $participants, ?string $contingent): array
    {
        return array_filter($participants, function (Participant $participant) use ($contingent): bool {
            return $participant->contingent === $contingent;
        });
    }

    public function closeRegistration(Participant $participant): Participant
    {
        if ($this->isCloseRegistrationValid($participant)) {
            $user = $participant->getUserButNotNull();
            $participant->registrationCloseDate = new DateTimeImmutable();
            $this->participantRepository->persist($participant);
            $this->userService->closeRegistration($user);
            $this->mailer->sendRegistrationClosed($user);
        }

        return $participant;
    }

    public function cancelPayment(Payment $payment, string $reason): Payment
    {
        $this->paymentService->cancelPayment($payment);
        $this->userService->closeRegistration($payment->participant->getUserButNotNull());

        $this->mailer->sendCancelledPayment($payment->participant, $reason);

        return $payment;
    }

    public function denyRegistration(Participant $participant, string $reason): Participant
    {
        $this->mailer->sendDeniedRegistration($participant, $reason);
        $this->userService->openRegistration($participant->getUserButNotNull());

        return $participant;
    }

    public function approveRegistration(Participant $participant): Participant
    {
        $participant->registrationApproveDate = new DateTimeImmutable();
        $this->participantRepository->persist($participant);

        if ($participant instanceof Guest) {
            $this->userService->payRegistration($participant->getUserButNotNull());
            $this->mailer->sendGuestRegistrationFinished($participant);
            $this->flashMessages->success($this->translator->trans('flash.success.guestApproved'));

            return $participant;
        }

        $payment = $this->paymentService->createAndPersistNewPayment($participant);

        if ($participant->isInSpecialPaymentContingent()) {
            $this->mailer->sendRegistrationApprovedForForeignContingents($participant);
        } else {
            $this->mailer->sendRegistrationApprovedWithPayment($participant, $payment);
        }
        $this->userService->setUserApproved($participant->getUserButNotNull());
        $this->flashMessages->success($this->translator->trans('flash.success.approved'));

        return $participant;
    }


    /**
     * @param Participant[] $participants
     */
    public function generatePaymentsFor(array $participants): int
    {
        $count = 0;
        foreach ($participants as $participant) {
            $payment = $this->paymentService->createAndPersistNewPayment($participant);
            $this->userService->setUserApproved($participant->getUserButNotNull());
            $this->mailer->sendRegistrationApprovedWithNonFirstPayment($participant, $payment);
            $count++;
        }

        return $count;
    }


    public function getContentArbiterForParticipant(
        Participant $participant,
    ): AbstractContentArbiter {
        $eventType = $participant->getUserButNotNull()->event->eventType;

        return match ($participant->role) {
            User::ROLE_PATROL_LEADER => $eventType->getContentArbiterPatrolLeader(),
            User::ROLE_PATROL_PARTICIPANT => $eventType->getContentArbiterPatrolParticipant(),
            User::ROLE_TROOP_LEADER => $eventType->getContentArbiterTroopLeader(),
            User::ROLE_TROOP_PARTICIPANT => $eventType->getContentArbiterTroopParticipant(),
            User::ROLE_IST => $eventType->getContentArbiterIst(),
            User::ROLE_GUEST => $eventType->getContentArbiterGuest(),
            default => throw new \RuntimeException('Unexpected role ' . $participant->role),
        };
    }
}
