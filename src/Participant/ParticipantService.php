<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Application\DateTimeUtils;
use kissj\Event\AbstractContentArbiter;
use kissj\Event\ContentArbiter\ContentArbiterItemType;
use kissj\Event\Event;
use kissj\Mailer\Mailer;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Payment\Payment;
use kissj\Payment\PaymentService;
use kissj\Payment\PaymentStatus;
use kissj\User\UserLoginType;
use kissj\User\UserService;
use kissj\User\UserStatus;

readonly class ParticipantService
{
    public function __construct(
        private ParticipantRepository      $participantRepository,
        private TroopParticipantRepository $troopParticipantRepository,
        private PaymentService             $paymentService,
        private UserService                $userService,
        private Mailer                     $mailer,
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
        $p->emergencyContact = $params['emergencyContact'] ?? null;
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
        $rawPreferredPosition = $params['preferredPosition'] ?? [];
        /** @var list<string> $preferredPosition */
        $preferredPosition = is_array($rawPreferredPosition) ? $rawPreferredPosition : [];
        $p->preferredPosition = $preferredPosition;
        $p->driversLicense = $params['driversLicense'] ?? null;
        $p->printedHandbook = array_key_exists('printedHandbook', $params) ? true : null;
        $p->notes = $params['notes'] ?? null;

        return $p;
    }

    public function isCloseRegistrationValid(Participant $participant): RegistrationCloseResult
    {
        $result = RegistrationCloseResult::startChecking();
        $event = $participant->getUserButNotNull()->event;

        // TODO move check for patrol leader here

        if ($participant instanceof TroopLeader) {
            $result = $this->validateTroopLeaderRegistrationClose($participant, $event, $result);
        }

        if (!$this->isParticipantDataValidForClose($participant, $this->getContentArbiterForParticipant($participant))) {
            $result = $result->withWarning('flash.warning.noLock');
        }

        if ($event->eventType->isFullForParticipant(
            $participant,
            $this->getClosedSameRoleSameContingentParticipantsCount($participant),
        )) {
            // TODO fix problem with contingents
            // - in CEJ we want to limit each contingent by its own, but
            // - in Navigamus we want to count all contingents together
            $result = $result->withWarning('flash.warning.fullRegistration');
        }

        if (
            $event->maximalClosedParticipantsCount !== null
            && $this->getParticipantsComingToEventCount($event) >= $event->maximalClosedParticipantsCount
        ) {
            $result = $result->withWarning('flash.warning.fullRegistration');
        }

        if (!$participant instanceof TroopParticipant && !$event->canRegistrationBeLocked()) {
            $result = $result->withWarning('flash.warning.registrationNotAllowed', [
                '%difference%' => $event->startRegistration->diff(DateTimeUtils::getDateTime())->format('%H:%I:%S'),
            ]);
        }

        if ($event->eventType->enforceActiveSkautisMembership()
            && $participant->getUserButNotNull()->loginType === UserLoginType::Skautis
            && !(bool)$participant->getUserButNotNull()->skautisHasMembership) {
            $result = $result->withWarning('flash.warning.missingSkautisMembership');
        }

        return $result;
    }

    private function validateTroopLeaderRegistrationClose(
        TroopLeader $troopLeader,
        Event $event,
        RegistrationCloseResult $result,
    ): RegistrationCloseResult {
        $troopParticipants = $troopLeader->troopParticipants;

        $participantsCount = count($troopParticipants);
        if ($participantsCount < $event->getMinimalPpCount($troopLeader)) {
            $result = $result->withWarning('flash.warning.tlTooFewParticipantsTroop', [
                '%minimalTroopParticipantsCount%' => (string)$event->getMinimalPpCount($troopLeader),
            ]);
        }
        if ($participantsCount > $event->getMaximalPpCount($troopLeader)) {
            $result = $result->withWarning('flash.warning.tlTooManyParticipantsTroop', [
                '%maximalTroopParticipantsCount%' => (string)$event->getMaximalPpCount($troopLeader),
            ]);
        }

        foreach ($troopParticipants as $participant) {
            if (!$this->isParticipantDataValidForClose(
                $participant,
                $event->getEventType()->getContentArbiterTroopParticipant(),
            )) {
                $result = $result->withWarning('flash.warning.tlWrongDataParticipant', [
                    '%participantFullName%' => $participant->getFullName(),
                ]);
            }

            if ($participant->getUserButNotNull()->status === UserStatus::Open) {
                $result = $result->withWarning('flash.warning.tpNotClosed', [
                    '%participantFullName%' => $participant->getFullName(),
                ]);
            }
        }

        return $result;
    }

    public function isParticipantDataValidForClose(Participant $p, AbstractContentArbiter $ca): bool
    {
        foreach ($ca->getAllowedItems() as $item) {
            if (!$item->required) {
                continue;
            }

            if ($item->type === ContentArbiterItemType::File) {
                continue;
            }

            if ($item->type === ContentArbiterItemType::TshirtComposite) {
                if ($p->getTshirtShape() === null || $p->getTshirtSize() === null) {
                    return false;
                }
                continue;
            }

            if ($p->getValueForField($item->slug) === null) {
                return false;
            }
        }

        if ($ca->email->allowed && $p->email !== null && filter_var($p->email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        if ($ca->phone->allowed && $p->telephoneNumber !== null && preg_match('/^\+?[0-9 ]+$/', $p->telephoneNumber) === 0) {
            return false;
        }

        return true;
    }

    private function getClosedSameRoleSameContingentParticipantsCount(Participant $participant): int
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
        if ($this->isCloseRegistrationValid($participant)->isValid) {
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

    public function changePaymentPrice(Payment $oldPayment, int $newPrice, string $reason): Payment
    {
        if ($oldPayment->status !== PaymentStatus::Waiting) {
            throw new \RuntimeException('Cannot change price of payment that is not in waiting status');
        }

        $participant = $oldPayment->participant;

        $this->paymentService->cancelPayment($oldPayment);

        $newPayment = $this->paymentService->createAndPersistNewCustomPayment($participant, $newPrice, $reason);

        if ($newPrice === 0) {
            $this->paymentService->confirmPayment($newPayment);
        } else {
            $this->mailer->sendPaymentPriceChanged($participant, $newPayment, $reason);
        }

        return $newPayment;
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

        if ($participant instanceof TroopParticipant) {
            $this->mailer->sendTroopParticipantRegistrationFinished($participant);

            return $participant;
        }

        $event = $participant->getUserButNotNull()->event;
        $price = $event->getEventType()->getPrice($participant);

        if ($price === 0) {
            $this->userService->setUserPaid($participant->getUserButNotNull());
            $this->mailer->sendRegistrationApprovedNoPayment($participant);

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
        if ($participant->role === null) { // TODO remove nullable from role
            throw new \LogicException('Missing role for participant ID: ' . $participant->id);
        }

        return $participant->getUserButNotNull()->event->eventType->getContentArbiterForRole($participant->role);
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

    public function tryChangeRole(string $roleFromBody, Participant $participant, Event $event): RoleChangeResult
    {
        $role = ParticipantRole::tryFrom($roleFromBody);
        if ($role === null || !in_array($role, $event->getAvailableRoles(), true)) {
            return RoleChangeResult::RoleNotValid;
        }

        if ($participant instanceof PatrolLeader) {
            if ($participant->getPatrolParticipantsCount() > 0) {
                return RoleChangeResult::PatrolHasParticipants;
            }
        }

        if ($participant instanceof TroopLeader) {
            if ($participant->getTroopParticipantsCount() > 0) {
                return RoleChangeResult::TroopHasParticipants;
            }
        }

        if ($role === $participant->role) {
            return RoleChangeResult::SameRole;
        }

        if ($participant->getUserButNotNull()->status !== UserStatus::Open) {
            return RoleChangeResult::NotOpen;
        }

        if ($participant instanceof PatrolParticipant) {
            $participant->patrolLeader = null;
        }

        if ($participant instanceof TroopParticipant) {
            $participant->troopLeader = null;
        }

        $participant->role = $role;
        $this->participantRepository->persist($participant);

        return RoleChangeResult::Success;
    }

    public function setAdminNote(Participant $participant, string $adminNote): Participant
    {
        $participant->adminNote = $adminNote;
        $this->participantRepository->persist($participant);

        return $participant;
    }
}
