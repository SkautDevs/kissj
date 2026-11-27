<?php

declare(strict_types=1);

namespace kissj\Programme;

use DateTimeInterface;
use Dibi\Fluent;
use Dibi\Row;
use kissj\Event\Event;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Participant\Participant;
use kissj\User\UserStatus;

/**
 * @table programme_enrolment
 *
 * @method ProgrammeEnrolment get(int $enrolmentId)
 * @method ProgrammeEnrolment getOneBy(mixed[] $criteria)
 * @method ProgrammeEnrolment[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method ProgrammeEnrolment|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 * @method ProgrammeEnrolment|null findWithLock(int $enrolmentId)
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

        $grouped = [];
        foreach ($this->fetchEnrolments($qb) as $enrolment) {
            /** @var int|string $programmeId */
            $programmeId = $enrolment->getRowData()['programme_id'];
            $grouped[(int)$programmeId][] = $enrolment;
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

        return $this->fetchEnrolments($qb);
    }

    /**
     * @return list<ProgrammeEnrolment>
     */
    public function findActiveForProgramme(Programme $programme): array
    {
        $qb = $this->createFluent();
        $qb->where('programme_id = %i', $programme->id);
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
        $qb->where('u.status != %s', UserStatus::Cancelled->value);
        $qb->orderBy('programme_enrolment.participant_id')->asc();

        return $this->fetchEnrolments($qb);
    }

    public function findActive(Programme $programme, Participant $participant): ?ProgrammeEnrolment
    {
        $qb = $this->createFluent();
        $qb->where('programme_id = %i', $programme->id);
        $qb->where('participant_id = %i', $participant->id);

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

    /**
     * @return array<int, int>
     */
    public function countActiveByProgramme(Event $event): array
    {
        $qb = $this->connection->select('pr.programme_id, COUNT(*) AS taken')
            ->from($this->getTable())->as('pr')
            ->join('programme')->as('p')->on('p.id = pr.programme_id')
            ->where('p.event_id = %i', $event->id)
            ->where('pr.deleted_at IS NULL')
            ->where('p.deleted_at IS NULL')
            ->groupBy('pr.programme_id');

        /** @var array<int, int> $occupancy */
        $occupancy = $qb->fetchPairs('programme_id', 'taken');

        return $occupancy;
    }

    public function countParticipantsWithOverlappingInSection(ProgrammeSection $section): int
    {
        /** @var int $count */
        $count = $this->connection->select('COUNT(DISTINCT pr1.participant_id)')
            ->from($this->getTable())->as('pr1')
            ->join('programme')->as('p1')->on('p1.id = pr1.programme_id')
            ->join($this->getTable())->as('pr2')
            ->on('pr2.participant_id = pr1.participant_id AND pr2.id <> pr1.id')
            ->join('programme')->as('p2')->on('p2.id = pr2.programme_id')
            ->join('participant')->as('pa')->on('pa.id = pr1.participant_id')
            ->leftJoin('participant')->as('pl')->on('pl.id = pa.patrol_leader_id')
            ->join('user')->as('u')->on('u.id = COALESCE(pa.user_id, pl.user_id)')
            ->where('p1.section_id = %i', $section->id)
            ->where('p2.section_id = %i', $section->id)
            ->where('pr1.deleted_at IS NULL')
            ->where('pr2.deleted_at IS NULL')
            ->where('p1.deleted_at IS NULL')
            ->where('p2.deleted_at IS NULL')
            ->where('u.status != %s', UserStatus::Cancelled->value)
            ->where('p1.start < [p2.end]')
            ->where('p2.start < [p1.end]')
            ->fetchSingle();

        return $count;
    }

    public function create(Programme $programme, Participant $participant): ProgrammeEnrolment
    {
        $enrolment = new ProgrammeEnrolment();
        $enrolment->programme = $programme;
        $enrolment->participant = $participant;
        $this->persist($enrolment);

        return $enrolment;
    }

    public function softDelete(ProgrammeEnrolment $enrolment, DateTimeInterface $deletedAt): void
    {
        $enrolment->deletedAt = $deletedAt;
        $this->persist($enrolment);
    }

    public function softDeleteAllActiveForParticipant(Participant $participant, DateTimeInterface $deletedAt): int
    {
        return $this->softDeleteAll($this->findActiveForParticipant($participant), $deletedAt);
    }

    public function softDeleteAllActiveForProgramme(Programme $programme, DateTimeInterface $deletedAt): int
    {
        return $this->softDeleteAll($this->findActiveForProgramme($programme), $deletedAt);
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

    /**
     * @param list<ProgrammeEnrolment> $enrolments
     */
    private function softDeleteAll(array $enrolments, DateTimeInterface $deletedAt): int
    {
        foreach ($enrolments as $enrolment) {
            $this->softDelete($enrolment, $deletedAt);
        }

        return count($enrolments);
    }
}
