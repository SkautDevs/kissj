<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Application\DateTimeUtils;
use kissj\Event\AbstractContentArbiter;
use kissj\Event\Event;
use kissj\FileHandler\SaveFileHandler;
use kissj\FileHandler\UploadFileHandler;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Mailer\Mailer;
use kissj\Participant\Guest\Guest;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
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

readonly class ParticipantService
{
    public function __construct(
        private ParticipantRepository $participantRepository,
        private TroopParticipantRepository $troopParticipantRepository,
        private PaymentService $paymentService,
        private UserService $userService,
        private FlashMessagesBySession $flashMessages,
        private TranslatorInterface $translator,
        private Mailer $mailer,
        private SaveFileHandler $saveFileHandler,
        private UploadFileHandler $uploadFileHandler,
    ) {
    }

    /**
     * @param array<string, string|null> $params
     */
    public function addParamsIntoParticipant(Participant $participant, array $params): Participant
    {
        $participant = $this->addParamsIntoPerson($params, $participant);
        $this->participantRepository->persist($participant);

        return $participant;
    }

    /**
     * @param array<string, string|null> $params
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
            $uploadedFile = $this->uploadFileHandler->resolveUploadedFile($request);
            if ($uploadedFile instanceof UploadedFile) {
                $this->saveFileHandler->saveFileTo($participant, $uploadedFile);
            }
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
            $this->flashMessages->warning('flash.warning.noLock');

            $validityFlag = false;
        }

        if (
            $this->getClosedSameRoleParticipantsCount($participant)
            >= $event->eventType->getMaximumClosedParticipants($participant)
            // TODO fix problem with contingents
            // - in CEJ we want to limit each contingent by its own, but
            // - in Navigamus we want to count all contingents together
        ) {
            $this->flashMessages->warning('flash.warning.fullRegistration');

            $validityFlag = false;
        }

        if (
            $event->maximalClosedParticipantsCount !== null
            && $this->getParticipantsComingToEventCount($event) >= $event->maximalClosedParticipantsCount
        ) {
            $this->flashMessages->warning('flash.warning.fullRegistration');

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
        if ($participantsCount < $event->getMinimalPpCount($troopLeader)) {
            $this->flashMessages->warning(
                $this->translator->trans(
                    'flash.warning.tlTooFewParticipantsTroop',
                    ['%minimalTroopParticipantsCount%' => $event->getMinimalPpCount($troopLeader)],
                )
            );

            $validityFlag = false;
        }
        if ($participantsCount > $event->getMaximalPpCount($troopLeader)) {
            $this->flashMessages->warning(
                $this->translator->trans(
                    'flash.warning.tlTooManyParticipantsTroop',
                    ['%maximalTroopParticipantsCount%' => $event->getMaximalPpCount($troopLeader)],
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

        if ($ca->email && $p->email !== null && filter_var($p->email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        // numbers and plus sight up front only
        if ($ca->phone && ($p->telephoneNumber === null || preg_match('/^\+?[0-9 ]+$/', $p->telephoneNumber) === 0)) {
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
            ParticipantRole::all(),
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
     * @return Participant[]
     */
    private function filterSameContingent(array $participants, ?string $contingent): array
    {
        return array_filter(
            $participants,
            fn (Participant $participant): bool => $participant->contingent === $contingent,
        );
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

    public function addNewPayment(Participant $participant, int $price, string $reason): Payment
    {
        $payment = $this->paymentService->createAndPersistNewCustomPayment($participant, $price, $reason);
        $this->userService->setUserApproved($participant->getUserButNotNull());
        $this->mailer->sendRegistrationApprovedWithNonFirstPayment($participant, $payment);

        return $payment;
    }

    public function cancelPayment(Payment $payment, string $reason): Payment
    {
        $this->paymentService->cancelPayment($payment);
        $participant = $payment->participant;

        if ($participant->countWaitingPayments() === 0) {
            if ($participant->countPaidPayments() > 0) {
                $this->userService->setUserPaid($participant->getUserButNotNull());
                if ($participant instanceof TroopLeader) {
                    foreach ($participant->troopParticipants as $tp) {
                        $this->userService->setUserPaid($tp->getUserButNotNull());
                    }
                }
            } else {
                $this->userService->setUserClosed($participant->getUserButNotNull());
                if ($participant instanceof TroopLeader) {
                    foreach ($participant->troopParticipants as $tp) {
                        $this->userService->setUserClosed($tp->getUserButNotNull());
                    }
                }
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

    /**
     * @throws ParticipantException
     */
    public function approveRegistration(Participant $participant): Participant
    {
        if ($participant->getUserButNotNull()->status === UserStatus::Approved) {
            throw new ParticipantException('flash.warning.notApproved');
        }

        $this->userService->setUserApproved($participant->getUserButNotNull());

        $participant->registrationApproveDate = DateTimeUtils::getDateTime();
        $this->participantRepository->persist($participant);

        if ($participant instanceof Guest) {
            $this->userService->setUserPaid($participant->getUserButNotNull());
            $this->mailer->sendGuestRegistrationFinished($participant);

            return $participant;
        }

        if ($participant instanceof TroopParticipant) {
            $this->mailer->sendTroopParticipantRegistrationFinished($participant);

            return $participant;
        }

        $payment = $this->paymentService->createAndPersistNewEventPayment($participant);
        $this->mailer->sendRegistrationApprovedWithPayment($participant, $payment);

        return $participant;
    }

    public function cancelParticipant(Participant $participant): Participant
    {
        $this->userService->setUserCancelled($participant->getUserButNotNull());

        return $participant;
    }

    public function uncancelParticipant(Participant $participant): Participant
    {
        $this->userService->setUserPaid($participant->getUserButNotNull());

        return $participant;
    }

    /**
     * @param Participant[] $participants
     * TODO check if works correctly
     */
    public function generatePaymentsFor(array $participants): int
    {
        $count = 0;
        foreach ($participants as $participant) {
            $payment = $this->paymentService->createAndPersistNewEventPayment($participant);
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

    public function setAsUnentered(Participant $participant): Participant
    {
        $participant->entryDate = null;
        $this->participantRepository->persist($participant);

        return $participant;
    }
    public function setAsLeaved(Participant $participant): Participant
    {
        $participant->leaveDate = DateTimeUtils::getDateTime();
        $this->participantRepository->persist($participant);

        return $participant;
    }

    public function setAsUnleaved(Participant $participant): Participant
    {
        $participant->leaveDate = null;
        $this->participantRepository->persist($participant);

        return $participant;
    }

    public function tryChangeRoleWithMessages(string $roleFromBody, Participant $participant, Event $event): bool
    {
        $role = ParticipantRole::tryFrom($roleFromBody);
        if ($role === null || !in_array($role, $event->getAvailableRoles(), true)) {
            $this->flashMessages->error('flash.error.roleNotValid');

            return false;
        }

        if ($participant instanceof PatrolLeader) {
            if ($participant->getPatrolParticipantsCount() > 0) {
                $this->flashMessages->warning('flash.warning.patrolHasParticipantsCannotChangeRole');

                return false;
            }
        }

        if ($participant instanceof TroopLeader) {
            if ($participant->getTroopParticipantsCount() > 0) {
                $this->flashMessages->warning('flash.warning.troopHasParticipantsCannotChangeRole');

                return false;
            }
        }

        if ($role === $participant->role) {
            $this->flashMessages->warning('flash.warning.sameRoleCannotChangeRole');

            return false;
        }

        if ($participant->getUserButNotNull()->status !== UserStatus::Open) {
            $this->flashMessages->warning('flash.warning.notOpenCannotChangeRole');

            return false;
        }

        if ($participant instanceof PatrolParticipant) {
            $participant->patrolLeader = null;
        }

        if ($participant instanceof TroopParticipant) {
            $participant->troopLeader = null;
        }

        $participant->role = $role;
        $this->participantRepository->persist($participant);

        $this->flashMessages->success('flash.success.roleChanged');

        return true;
    }

    public function setAdminNote(Participant $participant, string $adminNote): Participant
    {
        $participant->adminNote = $adminNote;
        $this->participantRepository->persist($participant);

        return $participant;
    }
}
