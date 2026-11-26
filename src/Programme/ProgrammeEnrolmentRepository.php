<?php

declare(strict_types=1);

namespace kissj\Programme;

use Dibi\Fluent;
use Dibi\Row;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Participant\Participant;
use kissj\User\UserStatus;
use LogicException;

/**
 * @table programme_enrolment
 *
 * @method ProgrammeEnrolment get(int $enrolmentId)
 * @method ProgrammeEnrolment getOneBy(mixed[] $criteria)
 * @method ProgrammeEnrolment[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method ProgrammeEnrolment|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class ProgrammeEnrolmentRepository extends Repository
{
    /**
     * @return array<int, list<ProgrammeEnrolment>>
     */
    public function findActiveForEventGroupedByProgramme(Event $event): array
    {
        $qb = $this->createFluent();
        $qb->join('programme')->as('p')->on('p.id = programme_enrolment.programme_id');
        $qb->where('p.event_id = %i', $event->id);
        $qb->where('p.deleted_at IS NULL');
        $qb->where('programme_enrolment.deleted_at IS NULL');

        $grouped = [];
        foreach ($qb->fetchAll() as $row) {
            /** @var Row&iterable<string, mixed> $row */
            $programmeIdRaw = $row['programme_id'] ?? null;
            if (is_numeric($programmeIdRaw) === false) {
                throw new LogicException('programme_enrolment row without numeric programme_id');
            }
            /** @var ProgrammeEnrolment $enrolment */
            $enrolment = $this->createEntity($row);
            $grouped[(int)$programmeIdRaw][] = $enrolment;
        }

        return $grouped;
    }

    /**
     * @return list<ProgrammeEnrolment>
     */
    public function findActiveForParticipant(Participant $participant): array
    {
        $qb = $this->createFluent();
        $qb->where('participant_id = %i', $participant->id);
        $qb->where('deleted_at IS NULL');

        return $this->fetchEnrolments($qb);
    }

    /**
     * @return list<ProgrammeEnrolment>
     */
    public function findActiveForProgramme(Programme $programme): array
    {
        $qb = $this->createFluent();
        $qb->where('programme_id = %i', $programme->id);
        $qb->where('deleted_at IS NULL');
        $qb->orderBy('participant_id')->asc();

        return $this->fetchEnrolments($qb);
    }

    /**
     * @return list<ProgrammeEnrolment>
     */
    public function findActiveNotCancelledForProgramme(Programme $programme): array
    {
        $qb = $this->createFluent();
        $qb->join('participant')->as('pa')->on('pa.id = programme_enrolment.participant_id');
        $qb->leftJoin('participant')->as('pl')->on('pl.id = pa.patrol_leader_id');
        $qb->join('user')->as('u')->on('u.id = COALESCE(pa.user_id, pl.user_id)');
        $qb->where('programme_enrolment.programme_id = %i', $programme->id);
        $qb->where('programme_enrolment.deleted_at IS NULL');
        $qb->where('u.status != %s', UserStatus::Cancelled->value);
        $qb->orderBy('programme_enrolment.participant_id')->asc();

        return $this->fetchEnrolments($qb);
    }

    public function findActive(Programme $programme, Participant $participant): ?ProgrammeEnrolment
    {
        $qb = $this->createFluent();
        $qb->where('programme_id = %i', $programme->id);
        $qb->where('participant_id = %i', $participant->id);
        $qb->where('deleted_at IS NULL');

        return $this->fetchEnrolments($qb)[0] ?? null;
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

    public function softDelete(ProgrammeEnrolment $enrolment): void
    {
        $enrolment->deletedAt = DateTimeUtils::getDateTime();
        $this->persist($enrolment);
    }

    public function softDeleteAllActiveForParticipant(Participant $participant): void
    {
        foreach ($this->findActiveForParticipant($participant) as $enrolment) {
            $this->softDelete($enrolment);
        }
    }

    public function softDeleteAllActiveForProgramme(Programme $programme): void
    {
        foreach ($this->findActiveForProgramme($programme) as $enrolment) {
            $this->softDelete($enrolment);
        }
    }

    /**
     * @return list<ProgrammeEnrolment>
     */
    private function fetchEnrolments(Fluent $qb): array
    {
        /** @var list<Row> $rows */
        $rows = $qb->fetchAll();

        /** @var array<int, ProgrammeEnrolment> $enrolments */
        $enrolments = $this->createEntities($rows);

        return array_values($enrolments);
    }
}
