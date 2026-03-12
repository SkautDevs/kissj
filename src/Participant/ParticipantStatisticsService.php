<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Event\Event;
use kissj\Participant\Admin\StatisticUserValueObject;
use kissj\User\UserStatus;

readonly class ParticipantStatisticsService
{
    public function __construct(
        private ParticipantRepository $participantRepository,
    ) {
    }

    /**
     * @param string[] $contingents
     * @return array<string, StatisticUserValueObject>
     */
    public function getContingentStatistic(
        Event $event,
        ParticipantRole $role,
        array $contingents,
    ): array {
        $statistics = [];
        foreach ($contingents as $contingent) {
            $statistics[$contingent] = $this->getStatistic(
                $event,
                $role,
                $contingent,
            );
        }

        return $statistics;
    }

    public function getStatistic(
        Event $event,
        ParticipantRole $role,
        ?string $contingent = null,
    ): StatisticUserValueObject {
        return $this->participantRepository->getStatistic($event, $role, $contingent);
    }

    /**
     * @param list<ParticipantRole> $participantRole
     */
    public function createParticipantFoodPlanFromEvent(
        Event $event,
        bool $usePatrolAndTroopsAggregator,
        array $participantRole = [
            ParticipantRole::TroopLeader,
            ParticipantRole::PatrolLeader,
            ParticipantRole::TroopParticipant,
            ParticipantRole::PatrolParticipant,
            ParticipantRole::Ist,
            ParticipantRole::Guest,
        ],
    ): ParticipantFoodPlan {
        $eventParticipants = $this->participantRepository->getAllParticipantsWithStatus(
            $participantRole,
            [UserStatus::Paid],
            $event,
        );

        $eventParticipants = array_filter(
            $eventParticipants,
            function (Participant $participant): bool {
                return $participant->foodPreferences !== null;
            }
        );

        return new ParticipantFoodPlan($eventParticipants, $event, $usePatrolAndTroopsAggregator);
    }

    /**
     * @return array<string, int>
     */
    public function getDigestFoodStatistic(
        Event $event,
    ): array {
        return $this->participantRepository->getDigestFoodStatistic($event);
    }
}
