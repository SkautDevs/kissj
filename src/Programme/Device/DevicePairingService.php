<?php

declare(strict_types=1);

namespace kissj\Programme\Device;

use DateInterval;
use DateTimeInterface;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Participant\Participant;
use kissj\Telemetry\MetricName;
use kissj\Telemetry\Metrics;
use kissj\User\User;
use kissj\User\UserStatus;
use LogicException;
use Psr\Log\LoggerInterface;

readonly class DevicePairingService
{
    public const int MAX_PENDING_PER_EVENT = 1000;
    private const int MAX_FAILED_ATTEMPTS_PER_HOUR = 10;
    private const int MAX_USER_CODE_DRAWS = 10;

    public function __construct(
        private DevicePairingRepository $devicePairingRepository,
        private DevicePairingAttemptRepository $devicePairingAttemptRepository,
        private DeviceSessionRepository $deviceSessionRepository,
        private LoggerInterface $logger,
        private Metrics $metrics,
    ) {
    }

    public function isDeviceAccessAllowed(User $user): bool
    {
        return $user->event->hasProgrammeApi() && $user->status !== UserStatus::Cancelled;
    }

    public function start(Event $event, string $deviceId): ?DevicePairing
    {
        $pairing = $this->devicePairingRepository->transactional(
            function () use ($event, $deviceId): ?DevicePairing {
                $this->devicePairingRepository->lockForEvent($event);
                $now = DateTimeUtils::getDateTime();
                $this->devicePairingRepository->replacePendingOfDevice($event, $deviceId);
                $pendingCount = $this->devicePairingRepository->countPendingUnexpired($event, $now);
                if ($pendingCount >= self::MAX_PENDING_PER_EVENT) {
                    return null;
                }

                $pairing = new DevicePairing();
                $pairing->event = $event;
                $pairing->deviceId = $deviceId;
                $pairing->userCode = $this->drawFreeUserCode($event, $now);
                $pairing->deviceCode = DevicePairingCodes::generateDeviceCode();
                $pairing->status = DevicePairingStatus::Pending;
                $pairing->expiresAt = $now->add(new DateInterval(DevicePairing::LIFETIME));
                $this->devicePairingRepository->persist($pairing);

                return $pairing;
            },
        );
        if ($pairing === null) {
            $this->logger->warning('Device pairing refused, pending cap reached', ['eventId' => $event->id]);
            $this->metrics->count(MetricName::PairingsRefused, 1, ['event' => $event->slug, 'reason' => 'cap']);

            return null;
        }

        $this->metrics->count(MetricName::PairingsStarted, 1, ['event' => $event->slug]);

        return $pairing;
    }

    public function purgePending(Event $event): int
    {
        $count = $this->devicePairingRepository->deletePending($event);
        $this->logger->warning('Pending device pairings purged by admin', ['eventId' => $event->id, 'count' => $count]);
        $this->metrics->count(MetricName::PairingsPurged, $count, ['event' => $event->slug]);

        return $count;
    }

    public function poll(string $deviceCode, Event $event): DevicePairingPollResult
    {
        $pairingId = $this->devicePairingRepository->findByDeviceCode($deviceCode, $event)?->id;
        if ($pairingId === null) {
            return DevicePairingPollResult::withoutSession(DevicePairingPollStatus::Expired);
        }

        return $this->devicePairingRepository->transactional(
            fn (): DevicePairingPollResult => $this->pollUnderLock($pairingId),
        );
    }

    public function findPendingForParticipant(
        string $enteredCode,
        Event $event,
        Participant $participant,
    ): DevicePairing|DevicePairingRefusal {
        if ($this->isLockedOut($participant)) {
            return DevicePairingRefusal::TooManyAttempts;
        }

        return $this->findPendingByEnteredCode($enteredCode, $event, $participant);
    }

    public function decide(
        string $enteredCode,
        DevicePairingDecision $decision,
        Event $event,
        Participant $participant,
    ): ?DevicePairingRefusal {
        $pairing = $this->findPendingForParticipant($enteredCode, $event, $participant);
        if ($pairing instanceof DevicePairingRefusal) {
            return $pairing;
        }

        $isDecided = match ($decision) {
            DevicePairingDecision::Approve => $this->approve($pairing, $participant),
            DevicePairingDecision::Deny => $this->deny($pairing),
        };

        return $isDecided ? null : DevicePairingRefusal::CodeAlreadyUsed;
    }

    public function approve(DevicePairing $pairing, Participant $participant): bool
    {
        if ($this->settle($pairing, DevicePairingStatus::Approved, $participant) === false) {
            return false;
        }

        $this->logger->info('Device pairing approved', [
            'pairingId' => $pairing->id,
            'participantId' => $participant->id,
        ]);
        $this->metrics->count(MetricName::PairingsApproved, 1, ['event' => $pairing->event->slug]);

        return true;
    }

    public function deny(DevicePairing $pairing): bool
    {
        if ($this->settle($pairing, DevicePairingStatus::Denied, null) === false) {
            return false;
        }

        $this->logger->info('Device pairing denied', ['pairingId' => $pairing->id]);
        $this->metrics->count(MetricName::PairingsDenied, 1, ['event' => $pairing->event->slug]);

        return true;
    }

    private function settle(DevicePairing $pairing, DevicePairingStatus $status, ?Participant $participant): bool
    {
        return $this->devicePairingRepository->transactional(
            function () use ($pairing, $status, $participant): bool {
                $lockedPairing = $this->lockUndecidedPairing($pairing);
                if ($lockedPairing === null) {
                    return false;
                }

                $lockedPairing->status = $status;
                $lockedPairing->participant = $participant;
                $this->devicePairingRepository->persist($lockedPairing);

                return true;
            },
        );
    }

    private function findPendingByEnteredCode(
        string $entered,
        Event $event,
        Participant $participant,
    ): DevicePairing|DevicePairingRefusal {
        $now = DateTimeUtils::getDateTime();
        $userCode = DevicePairingCodes::normaliseUserCode($entered);
        $pairing = $this->devicePairingRepository->findPendingByUserCode($userCode, $event, $now);
        if ($pairing !== null) {
            return $pairing;
        }

        $after = $now->sub(new DateInterval(DevicePairing::LIFETIME));
        if ($this->devicePairingRepository->existsCreatedAfter($userCode, $event, $after)) {
            $this->metrics->count(MetricName::PairingsRefused, 1, ['event' => $event->slug, 'reason' => 'used_code']);

            return DevicePairingRefusal::CodeAlreadyUsed;
        }

        $attempt = new DevicePairingAttempt();
        $attempt->participant = $participant;
        $this->devicePairingAttemptRepository->persist($attempt);
        $this->devicePairingAttemptRepository->deleteOlderThan($participant, $now->modify('-1 hour'));
        $this->metrics->count(MetricName::PairingsRefused, 1, ['event' => $event->slug, 'reason' => 'invalid_code']);

        return DevicePairingRefusal::InvalidCode;
    }

    private function isLockedOut(Participant $participant): bool
    {
        $since = DateTimeUtils::getDateTime('-1 hour');
        $failedAttempts = $this->devicePairingAttemptRepository->countSince($participant, $since);
        if ($failedAttempts < self::MAX_FAILED_ATTEMPTS_PER_HOUR) {
            return false;
        }

        $this->logger->warning('Device pairing refused, participant locked out', [
            'participantId' => $participant->id,
        ]);
        $this->metrics->count(MetricName::PairingsRefused, 1, [
            'event' => $participant->getUserButNotNull()->event->slug,
            'reason' => 'lockout',
        ]);

        return true;
    }

    private function lockUndecidedPairing(DevicePairing $pairing): ?DevicePairing
    {
        $lockedPairing = $this->devicePairingRepository->findWithLock($pairing->id);
        if (
            $lockedPairing === null
            || $lockedPairing->status !== DevicePairingStatus::Pending
            || $lockedPairing->isExpired(DateTimeUtils::getDateTime())
        ) {
            return null;
        }

        return $lockedPairing;
    }

    private function pollUnderLock(int $pairingId): DevicePairingPollResult
    {
        $now = DateTimeUtils::getDateTime();
        $pairing = $this->devicePairingRepository->findWithLock($pairingId);
        if ($pairing === null || $pairing->isExpired($now)) {
            return DevicePairingPollResult::withoutSession(DevicePairingPollStatus::Expired);
        }

        if ($pairing->isPolledTooSoon($now)) {
            return DevicePairingPollResult::withoutSession(DevicePairingPollStatus::SlowDown);
        }

        $pairing->lastPolledAt = $now;
        $this->devicePairingRepository->persist($pairing);

        return match ($pairing->status) {
            DevicePairingStatus::Pending => DevicePairingPollResult::withoutSession(DevicePairingPollStatus::Pending),
            DevicePairingStatus::Denied => DevicePairingPollResult::withoutSession(DevicePairingPollStatus::Denied),
            DevicePairingStatus::Approved, DevicePairingStatus::Consumed => $this->issueSession($pairing, $now),
            DevicePairingStatus::Replaced => DevicePairingPollResult::withoutSession(DevicePairingPollStatus::Expired),
        };
    }

    private function issueSession(DevicePairing $pairing, DateTimeInterface $now): DevicePairingPollResult
    {
        $sessionToken = DevicePairingCodes::generateSessionToken();
        $session = $pairing->deviceSession ?? new DeviceSession();
        $session->participant = $pairing->participant
            ?? throw new LogicException('Approved device pairing has no participant');
        $session->tokenHash = DeviceSession::hashToken($sessionToken);
        $session->extend($now);
        $this->deviceSessionRepository->persist($session);

        if ($pairing->status === DevicePairingStatus::Approved) {
            $pairing->status = DevicePairingStatus::Consumed;
            $pairing->expiresAt = $now;
        }
        $pairing->deviceSession = $session;
        $this->devicePairingRepository->persist($pairing);

        return DevicePairingPollResult::approved($sessionToken, $session->expiresAt);
    }

    private function drawFreeUserCode(Event $event, DateTimeInterface $now): string
    {
        for ($draw = 0; $draw < self::MAX_USER_CODE_DRAWS; ++$draw) {
            $userCode = DevicePairingCodes::generateUserCode();
            if ($this->devicePairingRepository->findPendingByUserCode($userCode, $event, $now) === null) {
                return $userCode;
            }
        }

        throw new LogicException('Failed to draw a free device pairing user code');
    }
}
