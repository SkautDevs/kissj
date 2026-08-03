<?php

declare(strict_types=1);

namespace kissj\Event;

use Dibi\Row;
use kissj\Application\DateTimeUtils;
use kissj\Orm\Order;
use kissj\Orm\Relation;
use kissj\Orm\Repository;

/**
 * @method Event get(int $id)
 * @method Event getOneBy(mixed[] $criteria)
 * @method list<Event> findBy(mixed[] $criteria, Order[] $orders = [])
 * @method Event|null findOneBy(mixed[] $criteria, Order[] $orders = [])
 */
class EventRepository extends Repository
{
    /**
     * @return list<Event>
     */
    public function findActiveEvents(): array
    {
        return $this->findBy([
            'end_day' => new Relation(DateTimeUtils::getDateTime('-1 month')->format(DATE_ATOM), '>'),
        ]);
    }

    /**
     * @return list<Event>
     */
    public function findActiveAutopaymentsOnEvents(): array
    {
        return $this->findBy([
            'automatic_payment_pairing' => true,
            'start_registration' => new Relation(DateTimeUtils::getDateTime()->format(DATE_ATOM), '<'),
            'end_day' => new Relation(DateTimeUtils::getDateTime('-3 month')->format(DATE_ATOM), '>'),
        ]);
    }

    /**
     * @return list<Event>
     */
    public function findAll(): array
    {
        return $this->findBy([], [new Order('start_day')]);
    }

    public function findBySlug(string $eventSlug): ?Event
    {
        return $this->findOneBy(['slug' => $eventSlug]);
    }

    public function findByDealApiKey(string $apiKey): ?Event
    {
        return $this->findOneBy(['api_key_deals' => $apiKey]);
    }

    public function findByEntryApiKey(string $apiKey): ?Event
    {
        return $this->findOneBy(['api_key_entry' => $apiKey]);
    }

    /**
     * Matches either scoped vendor key column. Returns every event that matches so the
     * caller can detect ambiguity (a key accidentally reused across events/columns) instead
     * of silently picking one - see VendorApiKeyMiddleware.
     *
     * @return list<Event>
     */
    public function findAllByVendorApiKey(string $apiKey): array
    {
        $qb = $this->createFluent();
        $qb->where('api_key_vendor = %s OR api_key_vendor_health = %s', $apiKey, $apiKey);

        /** @var list<Row> $rows */
        $rows = $qb->fetchAll();

        $events = [];
        foreach ($rows as $row) {
            /** @var Row&iterable<string, mixed> $row */
            $entity = $this->createEntity($row);
            if ($entity instanceof Event) {
                $events[] = $entity;
            }
        }

        return $events;
    }

    public function generateNewOrganizingTeamRegistrationToken(Event $event): string
    {
        $event->organizingTeamRegistrationToken = bin2hex(random_bytes(16));
        $this->persist($event);

        return $event->organizingTeamRegistrationToken;
    }
}
