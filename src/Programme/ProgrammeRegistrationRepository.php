<?php

declare(strict_types=1);

namespace kissj\Programme;

use Dibi\Fluent;
use Dibi\Row;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Orm\Repository;
use kissj\Participant\Participant;
use LogicException;

/**
 * @table programme_registration
 */
class ProgrammeRegistrationRepository extends Repository
{
    /**
     * @return array<int, list<ProgrammeRegistration>>
     */
    public function findActiveForEventGroupedByProgramme(Event $event): array
    {
        $qb = $this->createFluent();
        $qb->join('programme')->as('p')->on('p.id = programme_registration.programme_id');
        $qb->where('p.event_id = %i', $event->id);
        $qb->where('p.deleted_at IS NULL');
        $qb->where('programme_registration.deleted_at IS NULL');

        $grouped = [];
        foreach ($qb->fetchAll() as $row) {
            /** @var Row&iterable<string, mixed> $row */
            $programmeIdRaw = $row['programme_id'] ?? null;
            if (is_numeric($programmeIdRaw) === false) {
                throw new LogicException('programme_registration row without numeric programme_id');
            }
            /** @var ProgrammeRegistration $registration */
            $registration = $this->createEntity($row);
            $grouped[(int)$programmeIdRaw][] = $registration;
        }

        return $grouped;
    }

    /**
     * @return list<ProgrammeRegistration>
     */
    public function findActiveForParticipant(Participant $participant): array
    {
        $qb = $this->createFluent();
        $qb->where('participant_id = %i', $participant->id);
        $qb->where('deleted_at IS NULL');

        return $this->fetchRegistrations($qb);
    }

    /**
     * @return list<ProgrammeRegistration>
     */
    public function findActiveForProgramme(Programme $programme): array
    {
        $qb = $this->createFluent();
        $qb->where('programme_id = %i', $programme->id);
        $qb->where('deleted_at IS NULL');

        return $this->fetchRegistrations($qb);
    }

    public function findActive(Programme $programme, Participant $participant): ?ProgrammeRegistration
    {
        $qb = $this->createFluent();
        $qb->where('programme_id = %i', $programme->id);
        $qb->where('participant_id = %i', $participant->id);
        $qb->where('deleted_at IS NULL');

        return $this->fetchRegistrations($qb)[0] ?? null;
    }

    public function countActiveForProgramme(Programme $programme): int
    {
        $qb = $this->connection->select('count(*)')->from($this->getTable());
        $qb->where('programme_id = %i', $programme->id);
        $qb->where('deleted_at IS NULL');

        /** @var int $count */
        $count = $qb->fetchSingle();

        return $count;
    }

    public function softDelete(ProgrammeRegistration $registration): void
    {
        $registration->deletedAt = DateTimeUtils::getDateTime();
        $this->persist($registration);
    }

    public function softDeleteAllActiveForParticipant(Participant $participant): void
    {
        foreach ($this->findActiveForParticipant($participant) as $registration) {
            $this->softDelete($registration);
        }
    }

    public function softDeleteAllActiveForProgramme(Programme $programme): void
    {
        foreach ($this->findActiveForProgramme($programme) as $registration) {
            $this->softDelete($registration);
        }
    }

    /**
     * @return list<ProgrammeRegistration>
     */
    private function fetchRegistrations(Fluent $qb): array
    {
        /** @var list<Row> $rows */
        $rows = $qb->fetchAll();

        $registrations = [];
        foreach ($this->createEntities($rows) as $registration) {
            if ($registration instanceof ProgrammeRegistration) {
                $registrations[] = $registration;
            }
        }

        return $registrations;
    }
}
