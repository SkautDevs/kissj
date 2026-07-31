<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Entry\EntryStatus;
use kissj\Event\Event;
use kissj\Participant\Admin\StatisticUserValueObject;
use kissj\User\UserStatus;

readonly class ParticipantStatisticsService
{
    public const string NOT_SET_FOOD_KEY = 'foodStats-admin.notSet';

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
            ParticipantRole::OrganizingTeam,
        ],
    ): ParticipantFoodPlan {
        $eventParticipants = $this->participantRepository->getAllParticipantsWithStatus(
            $participantRole,
            [UserStatus::Paid],
            $event,
        );

        return $this->createParticipantFoodPlanFromParticipants($eventParticipants, $event, $usePatrolAndTroopsAggregator);
    }

    /**
     * @param Participant[] $participants
     */
    public function createParticipantFoodPlanFromParticipants(
        array $participants,
        Event $event,
        bool $usePatrolAndTroopsAggregator,
    ): ParticipantFoodPlan {
        $participantsWithFood = array_filter(
            $participants,
            function (Participant $participant): bool {
                return $participant->foodPreferences !== null;
            }
        );

        return new ParticipantFoodPlan($participantsWithFood, $event, $usePatrolAndTroopsAggregator);
    }

    /**
     * @param list<ParticipantRole> $roles
     * @return array{
     *     roles: list<string>,
     *     foodTypes: list<string>,
     *     matrix: array<string, array<string, int>>,
     *     rowTotals: array<string, int>,
     *     colTotals: array<string, int>,
     *     grandTotal: int,
     * }
     */
    public function getPresentFoodStatisticByRole(
        Event $event,
        array $roles,
    ): array {
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            $roles,
            [UserStatus::Paid],
            $event,
        );

        return $this->getPresentFoodStatisticFromParticipants($participants, $roles);
    }

    /**
     * @param Participant[] $participants
     * @param list<ParticipantRole> $roles
     * @return array{
     *     roles: list<string>,
     *     foodTypes: list<string>,
     *     matrix: array<string, array<string, int>>,
     *     rowTotals: array<string, int>,
     *     colTotals: array<string, int>,
     *     grandTotal: int,
     * }
     */
    public function getPresentFoodStatisticFromParticipants(
        array $participants,
        array $roles,
    ): array {
        // PatrolParticipants may be returned twice by the leader-status expansion, so dedupe by id.
        $deduplicatedParticipants = [];
        foreach ($participants as $participant) {
            $deduplicatedParticipants[$participant->id] = $participant;
        }

        $roleKeys = array_map(
            static fn (ParticipantRole $role): string => 'role.' . $role->value,
            $roles,
        );

        /** @var array<string, array<string, int>> $matrix */
        $matrix = array_fill_keys($roleKeys, []);
        $rowTotals = array_fill_keys($roleKeys, 0);
        /** @var array<string, int> $colTotals */
        $colTotals = [];
        /** @var list<string> $foodTypes */
        $foodTypes = [];
        $grandTotal = 0;

        foreach ($deduplicatedParticipants as $participant) {
            if (EntryStatus::entryFromDatetime($participant->entryDate, $participant->leaveDate)
                !== EntryStatus::ENTRY_STATUS_USED) {
                continue;
            }

            $roleKey = 'role.' . $participant->getRoleOrFail()->value;
            $foodKey = $participant->foodPreferences ?? self::NOT_SET_FOOD_KEY;

            if (!in_array($foodKey, $foodTypes, true)) {
                $foodTypes[] = $foodKey;
            }

            $matrix[$roleKey][$foodKey] = ($matrix[$roleKey][$foodKey] ?? 0) + 1;
            $rowTotals[$roleKey]++;
            $colTotals[$foodKey] = ($colTotals[$foodKey] ?? 0) + 1;
            $grandTotal++;
        }

        $foodTypes = array_values(array_diff($foodTypes, [self::NOT_SET_FOOD_KEY]));
        sort($foodTypes);
        $foodTypes[] = self::NOT_SET_FOOD_KEY;

        foreach ($roleKeys as $roleKey) {
            foreach ($foodTypes as $foodKey) {
                $matrix[$roleKey][$foodKey] ??= 0;
            }
        }
        foreach ($foodTypes as $foodKey) {
            $colTotals[$foodKey] ??= 0;
        }

        return [
            'roles' => $roleKeys,
            'foodTypes' => $foodTypes,
            'matrix' => $matrix,
            'rowTotals' => $rowTotals,
            'colTotals' => $colTotals,
            'grandTotal' => $grandTotal,
        ];
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
