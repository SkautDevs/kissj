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
    /** @var DatePeriod<DateTime>*/
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
     * @return DatePeriod<DateTime>
     */
    private function getDateRange(array $participants, DateTimeInterface $defaultStartDay, DateTimeInterface $defaultEndDay): DatePeriod
    {
        $firstDay = array_reduce($participants,
            function (DateTimeInterface $carry, Participant $participant) {
                if ($participant->arrivalDate === null) {
                    return $carry; // skip null/empty
                }
                return $participant->arrivalDate < $carry ? $participant->arrivalDate : $carry;
            }, $defaultStartDay);

        $lastDay = array_reduce($participants,
        function (DateTimeInterface $carry, Participant $participant) {
            if ($participant->arrivalDate === null) {
                return $carry; // skip null/empty
            }
            return $participant->arrivalDate > $carry ? $participant->arrivalDate : $carry;
        }, $defaultEndDay);

        return new DatePeriod(DateTime::createFromInterface($firstDay), new DateInterval('P1D'), DateTime::createFromInterface($lastDay)->modify('+1 day'));
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
            $role = $p->role;
            $diet = $p->foodPreferences;
            $arrival = $p->arrivalDate ?? $defaultStartDay;
            $departure = $p->departureDate ?? $defaultEndDay;

            foreach ($this->dates as $date) {
                if ($date < $arrival || $date > $departure) continue;

                if ($diet === null) continue;
                $dayKey = $date->format('j.n.');
                if (!isset($rows[$role->value  ?? 'p'][$dayKey])) {
                    $rows[$role->value  ?? 'p'][$dayKey] = array_fill_keys($this->dietTypes, 0);
                }

                $rows[$role->value  ?? 'p'][$dayKey][$diet]++;
            }
        }

        return $rows;
    }

    /**
     * @return array<string, array<string, array<string, array<int>>|string>>
     */
    public function toArray(): array {
        $dateKeys = [];
        foreach ($this->dates as $d) $dateKeys[] = $d->format('j.n.');
        return ['dates' => $dateKeys, 'diet_types' => $this->dietTypes, 'rows' => $this->rows];

    }
}


