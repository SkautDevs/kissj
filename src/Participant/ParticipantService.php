<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Application\DateTimeUtils;
use kissj\Event\AbstractContentArbiter;
use kissj\Event\Event;
use kissj\FileHandler\FileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\PhpMailerWrapper;
use kissj\Participant\Guest\Guest;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Payment\Payment;
use kissj\Payment\PaymentService;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParticipantService
{
    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly TroopParticipantRepository $troopParticipantRepository,
        private readonly PaymentService $paymentService,
        private readonly UserService $userService,
        private readonly FlashMessagesBySession $flashMessages,
        private readonly TranslatorInterface $translator,
        private readonly PhpMailerWrapper $mailer,
        private readonly FileHandler $fileHandler,
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
            $p->birthDate = DateTimeUtils::getDateTime($params['birthDate']);
        }
        $p->birthPlace = $params['birthPlace'] ?? null;
        $p->healthProblems = $params['healthProblems'] ?? null;
        $p->medicaments = $params['medicaments'] ?? null;
        $p->psychicalHealthProblems = $params['psychicalHealthProblems'] ?? null;
        $p->foodPreferences = $params['foodPreferences'] ?? null;
        $p->idNumber = $params['idNumber'] ?? null;
        $p->scarf = $params['scarf'] ?? null;
        $p->swimming = $params['swimming'] ?? null;
        $p->setTshirt($params['tshirtShape'] ?? null, $params['tshirtSize'] ?? null);
        if (array_key_exists('arrivalDate', $params) && $params['arrivalDate'] !== null) {
            $p->arrivalDate = DateTimeUtils::getDateTime($params['arrivalDate']);
        }
        if (array_key_exists('departureDate', $params) && $params['departureDate'] !== null) {
            $p->departureDate = DateTimeUtils::getDateTime($params['departureDate']);
        }
        $p->skills = $params['skills'] ?? null;
        /** @var string[] $preferredPosition */
        $preferredPosition = $params['preferredPosition'] ?? [];
        $p->preferredPosition = $preferredPosition;
        $p->driversLicense = $params['driversLicense'] ?? null;
        $p->printedHandbook = array_key_exists('printedHandbook', $params) ? true : null;
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

        // TODO move check for patrol leader here

        if ($participant instanceof TroopLeader) {
            $validityFlag = $this->isCloseRegistrationValidForTroopLeader($participant, $event);
        }

        if (!$this->isParticipantDataValidForClose($participant, $this->getContentArbiterForParticipant($participant))) {
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
            && $this->getParticipantsComingToEventCount($event) >= $event->maximalClosedParticipantsCount
        ) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.fullRegistration'));

            $validityFlag = false;
        }

        if (!$participant instanceof TroopParticipant && !$event->canRegistrationBeLocked()) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.registrationNotAllowed', [
                '%difference%' => $event->startRegistration->diff(DateTimeUtils::getDateTime())->format('%H:%I:%S'),
            ]));

            $validityFlag = false;
        }

        // to show all warnings
        return $validityFlag;
    }

    private function isCloseRegistrationValidForTroopLeader(TroopLeader $troopLeader, Event $event): bool
    {
        $validityFlag = true;
        $troopParticipants = $troopLeader->troopParticipants;

        $participantsCount = count($troopParticipants);
        if ($participantsCount < $event->minimalTroopParticipantsCount) {
            $this->flashMessages->warning(
                $this->translator->trans(
                    'flash.warning.tlTooFewParticipantsTroop',
                    ['%minimalTroopParticipantsCount%' => $event->minimalTroopParticipantsCount],
                )
            );

            $validityFlag = false;
        }
        if ($participantsCount > $event->maximalTroopParticipantsCount) {
            $this->flashMessages->warning(
                $this->translator->trans(
                    'flash.warning.tlTooManyParticipantsTroop',
                    ['%maximalTroopParticipantsCount%' => $event->maximalTroopParticipantsCount],
                )
            );

            $validityFlag = false;
        }

        foreach ($troopParticipants as $participant) {
            if (!$this->isParticipantDataValidForClose(
                $participant,
                $event->getEventType()->getContentArbiterTroopParticipant(),
            )) {
                $this->flashMessages->warning(
                    $this->translator->trans(
                        'flash.warning.tlWrongDataParticipant',
                        ['%participantFullName%' => $participant->getFullName()],
                    )
                );

                $validityFlag = false;
            }

            if ($participant->getUserButNotNull()->status === UserStatus::Open) {
                $this->flashMessages->warning(
                    $this->translator->trans(
                        'flash.warning.tpNotClosed',
                        ['%participantFullName%' => $participant->getFullName()],
                    )
                );

                $validityFlag = false;
            }
        }

        return $validityFlag;
    }

    public function isParticipantDataValidForClose(Participant $p, AbstractContentArbiter $ca): bool
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
        if ($ca->phone && (empty($p->telephoneNumber) || preg_match('/^\+?[0-9 ]+$/', $p->telephoneNumber) === 0)) {
            return false;
        }

        return true;
    }

    public function getClosedSameRoleParticipantsCount(Participant $participant): int
    {
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            $participant->role === null ? [] : [$participant->role],
            [
                UserStatus::Closed,
                UserStatus::Approved,
                UserStatus::Paid,
            ],
            $participant->getUserButNotNull()->event,
        );

        return count($this->filterSameContingent($participants, $participant->contingent));
    }

    public function getParticipantsComingToEventCount(Event $event): int
    {
        $allParticipants = $this->participantRepository->getParticipantsCount(
            [
                ParticipantRole::PatrolLeader,
                ParticipantRole::PatrolParticipant,
                ParticipantRole::TroopLeader,
                ParticipantRole::TroopParticipant,
                ParticipantRole::Ist,
                ParticipantRole::Guest,
            ],
            [
                UserStatus::Closed,
                UserStatus::Approved,
                UserStatus::Paid,
            ],
            $event,
        );

        $untiedParticipants = $this->troopParticipantRepository->getUnopenedUntiedCount($event);

        return $allParticipants - $untiedParticipants;
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
            $participant->registrationCloseDate = DateTimeUtils::getDateTime();
            $this->participantRepository->persist($participant);
            $this->userService->setUserClosed($user);
            $this->mailer->sendRegistrationClosed($user);
        }

        return $participant;
    }

    public function cancelPayment(Payment $payment, string $reason): Payment
    {
        $this->paymentService->cancelPayment($payment);
        $participant = $payment->participant;
        $this->userService->setUserClosed($participant->getUserButNotNull());
        if ($participant instanceof TroopLeader) {
            foreach ($participant->troopParticipants as $tp) {
                $this->userService->setUserClosed($tp->getUserButNotNull());
            }
        }

        $this->mailer->sendCancelledPayment($participant, $reason);

        return $payment;
    }

    public function denyRegistration(Participant $participant, string $reason): Participant
    {
        $this->mailer->sendDeniedRegistration($participant, $reason);
        $this->userService->setUserOpen($participant->getUserButNotNull());

        return $participant;
    }

    public function approveRegistration(Participant $participant): Participant
    {
        if ($participant->getUserButNotNull()->status === UserStatus::Approved) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.notApproved'));

            return $participant;
        }

        $this->userService->setUserApproved($participant->getUserButNotNull());

        $participant->registrationApproveDate = DateTimeUtils::getDateTime();
        $this->participantRepository->persist($participant);

        if ($participant instanceof Guest) {
            $this->userService->setUserPaid($participant->getUserButNotNull());
            $this->mailer->sendGuestRegistrationFinished($participant);
            $this->flashMessages->success($this->translator->trans('flash.success.guestApproved'));

            return $participant;
        }

        if ($participant instanceof TroopParticipant) {
            $this->mailer->sendTroopParticipantRegistrationFinished($participant);
            $this->flashMessages->success($this->translator->trans('flash.success.tpApproved'));

            return $participant;
        }

        $payment = $this->paymentService->createAndPersistNewPayment($participant);

        if ($participant->isInCzechContingent()) {
            $this->mailer->sendRegistrationApprovedForSpecialPayment($participant, $payment);
        } elseif ($participant->isInSpecialPaymentContingent()) {
            $this->mailer->sendRegistrationApprovedWithoutPayment($participant);
        } else {
            $this->mailer->sendRegistrationApprovedWithPayment($participant, $payment);
        }
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
            ParticipantRole::PatrolLeader => $eventType->getContentArbiterPatrolLeader(),
            ParticipantRole::PatrolParticipant => $eventType->getContentArbiterPatrolParticipant(),
            ParticipantRole::TroopLeader => $eventType->getContentArbiterTroopLeader(),
            ParticipantRole::TroopParticipant => $eventType->getContentArbiterTroopParticipant(),
            ParticipantRole::Ist => $eventType->getContentArbiterIst(),
            ParticipantRole::Guest => $eventType->getContentArbiterGuest(),
            null => throw new \RuntimeException('Missing role for participant ID: ' . $participant->id),
        };
    }

    public function setAsEntered(Participant $participant): Participant
    {
        $participant->entryDate = DateTimeUtils::getDateTime();
        $this->participantRepository->persist($participant);

        return $participant;
    }

    public function tryChangeRoleWithMessages(string $roleFromBody, Participant $participant, Event $event): bool
    {
        $role = ParticipantRole::tryFrom($roleFromBody);
        if ($role === null || !in_array($role, $event->getAvailableRoles(), true)) {
            $this->flashMessages->error($this->translator->trans('flash.error.roleNotValid'));

            return false;
        }

        if ($participant instanceof PatrolLeader) {
            if ($participant->getPatrolParticipantsCount() > 0) {
                $this->flashMessages->warning($this->translator->trans('flash.warning.patrolHasParticipantsCannotChangeRole'));

                return false;
            }
        }

        if ($participant instanceof TroopLeader) {
            if ($participant->getTroopParticipantsCount() > 0) {
                $this->flashMessages->warning($this->translator->trans('flash.warning.troopHasParticipantsCannotChangeRole'));

                return false;
            }
        }

        if ($role === $participant->role) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.sameRoleCannotChangeRole'));

            return false;
        }

        if ($participant->getUserButNotNull()->status !== UserStatus::Open) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.notOpenCannotChangeRole'));

            return false;
        }

        $participant->role = $role;
        $this->participantRepository->persist($participant);

        $this->flashMessages->success($this->translator->trans('flash.success.roleChanged'));

        return true;
    }
}
