<?php

declare(strict_types=1);

namespace kissj\Programme\Image;

use Dibi\Row;
use kissj\Application\DateTimeUtils;
use kissj\Orm\EntityNotFoundException;
use kissj\Orm\Order;
use kissj\Orm\Repository;
use kissj\Programme\Programme;

/**
 * @table programme_image
 *
 * @method ProgrammeImage get(int $imageId)
 * @method ProgrammeImage getOneBy(mixed[] $criteria)
 * @method ProgrammeImage[] findBy(mixed[] $criteria, Order[] $orders = [])
 * @method ProgrammeImage|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class ProgrammeImageRepository extends Repository
{
    /**
     * @param list<Programme> $programmes
     * @return array<int, list<ProgrammeImage>>
     */
    public function findForProgrammes(array $programmes): array
    {
        if ($programmes === []) {
            return [];
        }

        $qb = $this->createFluent();
        $qb->where(
            'programme_id IN %in',
            array_map(static fn (Programme $programme): int => $programme->id, $programmes),
        );
        $qb->orderBy('programme_id')->asc();
        $qb->orderBy('sort_order')->asc();
        $qb->orderBy('id')->asc();

        $imagesByProgramme = [];
        foreach ($this->hydrateImages($qb->fetchAll()) as $image) {
            // read the column, not $image->programme, which would load the programmes
            /** @var int|string $programmeId */
            $programmeId = $image->getRowData()['programme_id'];
            $imagesByProgramme[(int)$programmeId][] = $image;
        }

        return $imagesByProgramme;
    }

    /**
     * @return list<ProgrammeImage>
     */
    public function findForProgramme(Programme $programme): array
    {
        return $this->findForProgrammes([$programme])[$programme->id] ?? [];
    }

    public function findFirstForProgramme(Programme $programme): ?ProgrammeImage
    {
        return $this->findForProgramme($programme)[0] ?? null;
    }

    public function getForProgrammeById(int $imageId, Programme $programme): ProgrammeImage
    {
        $image = $this->findOneBy(['id' => $imageId, 'programme' => $programme]);
        if ($image === null) {
            throw new EntityNotFoundException('Programme image not found for programme');
        }

        return $image;
    }

    public function findServableByFilename(string $filename): ?ProgrammeImage
    {
        $qb = $this->createFluent();
        $qb->join('programme')->as('p')->on('p.id = programme_image.programme_id');
        $qb->join('event')->as('e')->on('e.id = p.event_id');
        $qb->where('programme_image.filename = %s', $filename);
        $qb->where('p.deleted_at IS NULL');
        $qb->where('e.api_key_event IS NOT NULL');
        $qb->orderBy('programme_image.id')->asc();
        $qb->limit(1);

        return $this->hydrateImages($qb->fetchAll())[0] ?? null;
    }

    public function countByFilename(string $filename): int
    {
        return $this->countBy(['filename' => $filename]);
    }

    public function getNextSortOrder(Programme $programme): int
    {
        /** @var int $nextSortOrder */
        $nextSortOrder = $this->connection->select('COALESCE(MAX(sort_order), -1) + 1')
            ->from($this->getTable())
            ->where('programme_id = %i', $programme->id)
            ->fetchSingle();

        return $nextSortOrder;
    }

    /**
     * Sets the thumbnail only when no other request has set one meanwhile, so two concurrent
     * lazy generations keep a single thumb file referenced.
     */
    public function fillThumbIfEmpty(ProgrammeImage $image, string $thumbFilename): bool
    {
        $this->connection->update($this->getTable(), [
            'thumb_filename' => $thumbFilename,
            'updated_at' => DateTimeUtils::formatAtom(DateTimeUtils::getDateTime()),
        ])
            ->where('id = %i', $image->id)
            ->where('thumb_filename IS NULL')
            ->execute();

        return $this->connection->getAffectedRows() === 1;
    }

    /**
     * @param array<int, Row> $rows
     * @return list<ProgrammeImage>
     */
    private function hydrateImages(array $rows): array
    {
        /** @var array<int, ProgrammeImage> $images */
        $images = $this->createEntities($rows);

        return array_values($images);
    }
}
