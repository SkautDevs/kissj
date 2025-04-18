<?php

namespace kissj\Participant;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeInterface;
use kissj\Event\Event;
use kissj\User\UserRole;

class ParticipantFoodPlan
{
    /** @var DatePeriod<DateTime, DateTime, null>*/
    public DatePeriod $dates;
    /** @var string[] */
    public array $dietTypes;
    /** @var array<string, array<string, array<string, int>>> */
    public array $rows;


    /**
     * @param Participant[] $participants
     * @param Event $event
     */
    public function __construct(array $participants, Event $event)
    {
        $this->dates = $this->getDateRange($participants, $event->startDay, $event->endDay);
        $this->dietTypes = array_unique(
            array_filter(
                array_column($participants, 'foodPreferences'),
                fn(?string $food) => !is_null($food)
            )
        );

        $this->rows = $this->buildPlan($participants, $event->startDay, $event->endDay);
    }

    /**
     * @param Participant[] $participants
     * @return DatePeriod<DateTime, DateTime, null>
     */
    private function getDateRange(array $participants, DateTimeInterface $defaultStartDay, DateTimeInterface $defaultEndDay): DatePeriod
    {
        $firstDay = array_reduce($participants,
            function (DateTimeInterface $carry, Participant $participant) {
                if ($participant->arrivalDate === null) {
                    return $carry; // skip null/empty
                }
                return $participant->arrivalDate < $carry ? $participant->arrivalDate : $carry;
            },
            $defaultStartDay);

        $lastDay = array_reduce($participants,
            function (DateTimeInterface $carry, Participant $participant) {
                if ($participant->departureDate === null) {
                    return $carry; // skip null/empty
                }
                return $participant->departureDate > $carry ? $participant->departureDate : $carry;
            },
            $defaultEndDay);

        return new DatePeriod(DateTime::createFromInterface($firstDay), new DateInterval('P1D'), DateTime::createFromInterface($lastDay), DatePeriod::INCLUDE_END_DATE);
    }

    /**
     * @param Participant[] $participants
     * @param DateTimeInterface $defaultStartDay
     * @param DateTimeInterface $defaultEndDay
     * @return array<string, array<string, array<string, int>>>
     */
    private function buildPlan(array $participants, DateTimeInterface $defaultStartDay, DateTimeInterface $defaultEndDay): array
    {
        $rows = [];

        foreach ($participants as $p) {
            $role = 'role.' . ($p->role->value ?? 'p'); // convert participantRole to translatable
            $diet = $p->foodPreferences;
            $arrival = $p->arrivalDate ?? $defaultStartDay;
            $departure = $p->departureDate ?? $defaultEndDay;

            foreach ($this->dates as $date) {
                if ($date < $arrival || $date > $departure) continue;

                if ($diet === null) continue;
                $dayKey = $date->format('j.n.');
                if (!isset($rows[$role][$dayKey])) {
                    $rows[$role][$dayKey] = array_fill_keys($this->dietTypes, 0);
                }

                $rows[$role][$dayKey][$diet]++;
            }
        }

        return $rows;
    }

    /**
     * array with all necessary data for foodstats-admin view render
     * @return array<string, array<array<string, array<string, int>>|string>>
     */
    public function toArray(): array {
        $dateKeys = [];
        foreach ($this->dates as $date) $dateKeys[] = $date->format('j.n.');

        $dietTypesIncludingSum = $this->dietTypes;
        $dietTypesIncludingSum[] = "foodStats-admin.daySum"; // include sum value for every day
        $temporaryRows = $this->rows;

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
     * @return list<list<string>>
     *
     */
    public function toCSV(): array {

        $newArray = [[], []];
        $dateKeys = [];
        $dietTypesCount = count($this->dietTypes);
        foreach ($this->dates as $date) $dateKeys[] = $date->format('j.n.');
        $newArray[0][] = "day";
        $newArray[1][] = "diet";
        foreach($dateKeys as $dateKey) {
            foreach($this->dietTypes as $dietType) {
                $newArray[0][] = $dateKey;
                $newArray[1][] = $dietType;
            }
        }

        foreach ($this->rows as $role => $row) {
            $newRow = [$role];
            foreach ($dateKeys as $dateKey) {
                foreach ($this->dietTypes as $dietType) if (isset($row[$dateKey])) {
                    $newRow[] =  (string)$row[$dateKey][$dietType];
                }
                else $newRow[] = "0";
            }
            $newArray[] = $newRow;
        }

        return $newArray;
    }

}



