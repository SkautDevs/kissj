<?php

namespace kissj\Participant;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use kissj\Event\Event;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;

class ParticipantFoodPlan
{
    /** @var DatePeriod<DateTime, DateTime, null> */
    public DatePeriod $dates;

    /** @var string[] */
    public array $dietTypes;

    /** @var bool */
    private bool $usePatrolAndTroopsAggregator;

    /** @var  array<int|string, string> */
    private array $participantPatrolTroopName; // patrol leader id => patrolName, so that we don't have to query db later

    /** @var array<string, array<string, array<string, int>>> */
    private array $aggregatedRows;


    /**
     * @param Participant[] $participants
     */
    public function __construct(array $participants, Event $event, bool $usePatrolAndTroopsAgreggator = false)
    {
        //normalize dates from event
        $defaultStartDay = DateTime::createFromInterface($event->startDay)->setTimezone(new DateTimeZone('UTC'))->setTime(0, 0);
        $defaultEndDay = DateTime::createFromInterface($event->endDay)->setTimezone(new DateTimeZone('UTC'))->setTime(0, 0);

        $this->dates = $this->getDateRange($participants, $defaultStartDay, $defaultEndDay, );
        $this->dietTypes = array_unique(
            array_filter(
                array_column($participants, 'foodPreferences'),
                fn (?string $food) => !is_null($food)
            )
        );

        $this->usePatrolAndTroopsAggregator = $usePatrolAndTroopsAgreggator;

        $this->aggregatedRows = $this->buildRoleAggregatedPlan($participants, $defaultStartDay, $defaultEndDay, );

    }

    /**
     * @param Participant[] $participants
     * @return DatePeriod<DateTime, DateTime, null>
     */
    private function getDateRange(
        array $participants,
        DateTimeInterface $defaultStartDay,
        DateTimeInterface $defaultEndDay,
    ): DatePeriod {
        $firstDay = array_reduce(
            $participants,
            function (DateTimeInterface $carry, Participant $participant) {
                if ($participant->arrivalDate === null) {
                    return $carry; // skip null/empty
                }

                return $participant->arrivalDate < $carry ? $participant->arrivalDate : $carry;
            },
            $defaultStartDay,
        );

        $lastDay = array_reduce(
            $participants,
            function (DateTimeInterface $carry, Participant $participant) {
                if ($participant->departureDate === null) {
                    return $carry; // skip null/empty
                }

                return $participant->departureDate > $carry ? $participant->departureDate : $carry;
            },
            $defaultEndDay,
        );

        // date period for which stats will be generated.
        return new DatePeriod(
            DateTime::createFromInterface($firstDay)->setTimezone(new DateTimeZone('UTC'))->setTime(0, 0),
            new DateInterval('P1D'),
            DateTime::createFromInterface($lastDay)->setTimezone(new DateTimeZone('UTC'))->setTime(0, 0),
            DatePeriod::INCLUDE_END_DATE,
        );
    }

    /**
     * @param Participant[] $participants
     * @return array<string, array<string, array<string, int>>>
     */
    private function buildRoleAggregatedPlan(
        array $participants,
        DateTimeInterface $defaultStartDay,
        DateTimeInterface $defaultEndDay,
    ): array {
        $rows = [];

        if ($this->usePatrolAndTroopsAggregator) {
            $this->participantPatrolTroopName = [];

            foreach ($participants as $p) {
                if ($p instanceof PatrolLeader || $p instanceof TroopLeader) {
                    $patrolOrTroopLeaderName = $p->patrolName ?? $p->getFullName();
                    $patrolOrTroopLeaderId = (string)$p->id;
                } elseif ($p instanceof PatrolParticipant) {
                    $patrolOrTroopLeaderName = $p->patrolLeader->patrolName ?? $p->getFullName();
                    $patrolOrTroopLeaderId = (string)$p->patrolLeader->id;
                } elseif ($p instanceof TroopParticipant) {
                    $patrolOrTroopLeaderName = $p->troopLeader->patrolName ?? $p->getFullName();
                    $patrolOrTroopLeaderId = (string)($p->troopLeader ?? $p)->id;
                } else {
                    continue;
                }

                $this->participantPatrolTroopName[$patrolOrTroopLeaderId] = $patrolOrTroopLeaderName;
                $rows = $this->insertParticipantIntoRows($p, $defaultStartDay, $defaultEndDay, $rows, $patrolOrTroopLeaderId);
            }
        } else {
            foreach ($participants as $p) {
                $role = 'role.' . ($p->role->value ?? 'p'); // convert participantRole to translatable
                $rows = $this->insertParticipantIntoRows($p, $defaultStartDay, $defaultEndDay, $rows, $role);
            }
        }

        return $rows;
    }

    /**
     * array with all necessary data for foodstats-admin view render
     *
     * @return array<string, array<array<string, array<string, int>>|string>>
     */
    public function roleAggregatedToArray(): array
    {
        if ($this->usePatrolAndTroopsAggregator) {
            return [];
        }

        $dateKeys = [];
        foreach ($this->dates as $date) {
            $dateKeys[] = $date->format('j.n.');
        }

        $dietTypesIncludingSum = $this->dietTypes;
        $dietTypesIncludingSum[] = "foodStats-admin.daySum"; // include sum value for every day
        $temporaryRows = $this->aggregatedRows;

        $summary = array_fill_keys($dateKeys, array_fill_keys($dietTypesIncludingSum, 0)); // fill summary with zeros

        foreach ($temporaryRows as $role => $row) {
            foreach ($dateKeys as $date) {
                if (isset($row[$date])) {
                    $sum = 0;
                    foreach ($dietTypesIncludingSum as $dietType) {
                        if (isset($row[$date][$dietType])) { // day summaries are not yet initialized at this point - so skip them
                            $summary[$date][$dietType] += $row[$date][$dietType];
                            $sum += $row[$date][$dietType];
                        }
                    }

                    $temporaryRows[$role][$date]["foodStats-admin.daySum"] = $sum;
                    $summary[$date]["foodStats-admin.daySum"] += $sum;
                }
            }
        }

        $temporaryRows["foodStats-admin.summary"] = $summary;

        return ['dates' => $dateKeys, 'diet_types' => $dietTypesIncludingSum, 'rows' => $temporaryRows];
    }

    /**
     * Food plan array containing count of foodPreferences aggregated by role and day
     * intended to be converted into CSV
     *
     * @return list<list<string>>
     */
    public function aggregatedToCSV(): array
    {
        $newArray = [[], []];
        $dateKeys = [];

        foreach ($this->dates as $date) {
            $dateKeys[] = $date->format('j.n.');
        }
        $newArray[0][] = "day";
        $newArray[1][] = "diet";
        foreach ($dateKeys as $dateKey) {
            foreach ($this->dietTypes as $dietType) {
                $newArray[0][] = $dateKey;
                $newArray[1][] = $dietType;
            }
        }

        foreach ($this->aggregatedRows as $aggregator => $row) {
            if ($this->usePatrolAndTroopsAggregator) {
                $aggregator = $this->participantPatrolTroopName[$aggregator];
            }

            $newRow = [$aggregator];
            foreach ($dateKeys as $dateKey) {
                foreach ($this->dietTypes as $dietType) {
                    if (isset($row[$dateKey])) {
                        $newRow[] =  (string)$row[$dateKey][$dietType];
                    } else {
                        $newRow[] = '0';
                    }
                }
            }
            $newArray[] = $newRow;
        }

        return $newArray;
    }

    /**
     * @param array<string, array<string, array<string, int>>> $rows
     *
     * @return array<string, array<string, array<string, int>>>
     */
    private function insertParticipantIntoRows(
        Participant $p,
        DateTimeInterface $defaultStartDay,
        DateTimeInterface $defaultEndDay,
        array $rows,
        string $aggregator,
    ): array {
        $diet = $p->foodPreferences;
        $arrival = $p->arrivalDate ?? $defaultStartDay;
        $departure = $p->departureDate ?? $defaultEndDay;

        foreach ($this->dates as $date) {
            if ($date < $arrival || $date > $departure) {
                continue;
            }

            if ($diet === null) {
                continue;
            }
            $dayKey = $date->format('j.n.');
            if (!isset($rows[$aggregator][$dayKey])) {
                $rows[$aggregator][$dayKey] = array_fill_keys($this->dietTypes, 0);
            }

            $rows[$aggregator][$dayKey][$diet]++;
        }
        return $rows;
    }
}
