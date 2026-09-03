<?php

declare(strict_types=1);

use kissj\Application\DateTimeUtils;
use Phinx\Seed\AbstractSeed;
use Ramsey\Uuid\Uuid;

class TestingEventsSeed extends AbstractSeed
{
    private const string CREATED_AT = '2026-09-01 10:00:00';

    // shared bank details for every seeded event, reused for both event rows and their payments
    private const string ACCOUNT_NUMBER = '123456789/0800';
    private const string IBAN = 'CZ6508000000192000145399';
    private const string SWIFT = 'GIBACZPX';
    private const string CONSTANT_SYMBOL = '0558';

    /** @var list<array{0: string, 1: string, 2: string}> first name, last name, gender, indexed 0..19 for n 1..20 */
    private const array NAME_POOL = [
        ['Jan', 'Novák', 'man'],
        ['Marie', 'Svobodová', 'woman'],
        ['Petr', 'Dvořák', 'man'],
        ['Jana', 'Černá', 'woman'],
        ['Josef', 'Procházka', 'man'],
        ['Eva', 'Kučerová', 'woman'],
        ['Pavel', 'Veselý', 'man'],
        ['Hana', 'Horáková', 'woman'],
        ['Martin', 'Němec', 'man'],
        ['Lenka', 'Marková', 'woman'],
        ['Tomáš', 'Pospíšil', 'man'],
        ['Alena', 'Pokorná', 'woman'],
        ['Jiří', 'Hájek', 'man'],
        ['Lucie', 'Králová', 'woman'],
        ['Ondřej', 'Jelínek', 'man'],
        ['Kateřina', 'Růžičková', 'woman'],
        ['Lukáš', 'Beneš', 'man'],
        ['Tereza', 'Fialová', 'woman'],
        ['Michal', 'Sedláček', 'man'],
        ['Barbora', 'Doležalová', 'woman'],
    ];

    /** role => its position in the fixed payment role ordinal list */
    private const array ROLE_ORDINALS = [
        'pl' => 1,
        'pp' => 2,
        'tl' => 3,
        'tp' => 4,
        'ist' => 5,
        'guest' => 6,
        'ot' => 7,
    ];

    /** @var list<string> food_preferences cycled by n % 3 */
    private const array FOOD_PREFERENCES = ['detail.foodWithout', 'detail.foodVegetarian', 'detail.foodVegan'];

    /** @var list<string> navigamus3027 PL contingent cycled by n % 5 */
    private const array NAVIGAMUS_CONTINGENTS = [
        'detail.contingent.without',
        'detail.contingent.endeavour',
        'detail.contingent.adventure',
        'detail.contingent.discovery',
        'detail.contingent.resolution',
    ];

    /** @var list<string> cej3026 PL contingent cycled by n % 5 */
    private const array CEJ_CONTINGENTS = [
        'detail.contingent.czechia',
        'detail.contingent.slovakia',
        'detail.contingent.poland',
        'detail.contingent.hungary',
        'detail.contingent.european',
    ];

    // explicit PP/TP index (1..20) => leader n it belongs to; PLs 1-5 stay open (no children)
    private const array CHILD_TO_LEADER = [
        1 => 16, 2 => 16, 3 => 17, 4 => 17, 5 => 18, 6 => 18, 7 => 19, 8 => 19, 9 => 20, 10 => 20,
        11 => 11, 12 => 12, 13 => 13, 14 => 14, 15 => 15,
        16 => 6, 17 => 7, 18 => 8, 19 => 9, 20 => 10,
    ];

    /** @var list<array<string, mixed>> */
    private const array EVENTS = [
        [
            'slug' => 'korbo3026',
            'readable_name' => 'Korbo 3026',
            'event_type' => 'korbo',
            'logo_url' => '/logo_korbo_1272.png',
            'start_day' => '2026-11-13',
            'end_day' => '2026-11-15',
            'prefix_variable_symbol' => 31,
            'roles' => ['ist'],
        ],
        [
            'slug' => 'navigamus3027',
            'readable_name' => 'Navigamus 3027',
            'event_type' => 'navigamus',
            'logo_url' => '/logo_navigamus27_color.png',
            'start_day' => '2027-06-03',
            'end_day' => '2027-06-06',
            'prefix_variable_symbol' => 32,
            'roles' => ['pl', 'ist', 'guest', 'ot'],
        ],
        [
            'slug' => 'obrok3027',
            'readable_name' => 'Obrok 3027',
            'event_type' => 'obrok',
            'logo_url' => '/logo_obrok27.png',
            'start_day' => '2027-05-27',
            'end_day' => '2027-05-31',
            'prefix_variable_symbol' => 33,
            'roles' => ['tl', 'ist', 'guest', 'ot'],
        ],
        [
            'slug' => 'nsj3025',
            'readable_name' => 'NSJ 3025',
            'event_type' => 'nsj',
            'logo_url' => '/logo_osj25_white_transparent_180.png',
            'start_day' => '2026-08-10',
            'end_day' => '2026-08-16',
            'prefix_variable_symbol' => 34,
            'roles' => ['pl', 'ist', 'guest', 'ot'],
        ],
        [
            'slug' => 'cej3026',
            'readable_name' => 'CEJ 3026',
            'event_type' => 'cej',
            'logo_url' => '/logo_cej26_color_transparent_176.png',
            'start_day' => '2026-07-31',
            'end_day' => '2026-08-09',
            'prefix_variable_symbol' => 35,
            // cej cannot price guest/ot (EventTypeCej::transformPaymentPrice supports only PL/IST) - excluded here
            'roles' => ['pl', 'ist'],
        ],
    ];

    public function run(): void
    {
        $this->realignIdSequences();

        foreach (self::EVENTS as $eventDefinition) {
            /** @var string $slug */
            $slug = $eventDefinition['slug'];
            if ($this->eventExists($slug)) {
                $this->getOutput()->writeln('skipping existing event ' . $slug);
                continue;
            }
            $eventId = $this->insertEvent($eventDefinition);
            $this->insertUser($eventId, 'a@a.a', 'admin', 'open');

            foreach ($eventDefinition['roles'] as $role) {
                if ($role === 'pl') {
                    $this->insertPatrols($eventId, $slug, $eventDefinition);
                    continue;
                }
                if ($role === 'tl') {
                    $this->insertTroops($eventId, $slug, $eventDefinition);
                    continue;
                }
                // remaining roles are ist/guest/ot — pl/tl are handled above
                for ($n = 1; $n <= 20; $n++) {
                    $status = $this->statusForIndex($n);
                    $email = sprintf('%s%d@%s.test', $role, $n, $slug);
                    $userId = $this->insertUser($eventId, $email, 'participant', $status);
                    $contingent = $this->contingentFor($slug, $role, $n);
                    $participantId = $this->insertParticipant($slug, $role, $n, $userId, null, $contingent);
                    $this->insertPaymentIfDue($participantId, $status, $slug, $role, $n, $eventDefinition);
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $eventDefinition
     */
    private function insertPatrols(int $eventId, string $slug, array $eventDefinition): void
    {
        $patrolLeaderIds = [];
        for ($n = 1; $n <= 20; $n++) {
            $status = $this->statusForIndex($n);
            $email = sprintf('pl%d@%s.test', $n, $slug);
            $userId = $this->insertUser($eventId, $email, 'participant', $status);
            $contingent = $this->contingentFor($slug, 'pl', $n);
            $participantId = $this->insertParticipant($slug, 'pl', $n, $userId, null, $contingent);
            $this->insertPaymentIfDue($participantId, $status, $slug, 'pl', $n, $eventDefinition);
            $patrolLeaderIds[$n] = $participantId;
        }

        for ($n = 1; $n <= 20; $n++) {
            $leaderN = self::CHILD_TO_LEADER[$n];
            // PPs have no user and never get payments
            $this->insertParticipant($slug, 'pp', $n, null, $patrolLeaderIds[$leaderN]);
        }
    }

    /**
     * @param array<string, mixed> $eventDefinition
     */
    private function insertTroops(int $eventId, string $slug, array $eventDefinition): void
    {
        $troopLeaderIds = [];
        for ($n = 1; $n <= 20; $n++) {
            $status = $this->statusForIndex($n);
            $email = sprintf('tl%d@%s.test', $n, $slug);
            $userId = $this->insertUser($eventId, $email, 'participant', $status);
            $participantId = $this->insertParticipant($slug, 'tl', $n, $userId, null);
            $this->insertPaymentIfDue($participantId, $status, $slug, 'tl', $n, $eventDefinition);
            $troopLeaderIds[$n] = $participantId;
        }

        for ($n = 1; $n <= 20; $n++) {
            $leaderN = self::CHILD_TO_LEADER[$n];
            $leaderStatus = $this->statusForIndex($leaderN);
            $email = sprintf('tp%d@%s.test', $n, $slug);
            $userId = $this->insertUser($eventId, $email, 'participant', $leaderStatus);
            // TPs reach paid via the leader's payment cascade - the app never creates a TP payment directly
            $this->insertParticipant(
                $slug,
                'tp',
                $n,
                $userId,
                $troopLeaderIds[$leaderN],
                statusOverride: $leaderStatus,
            );
        }
    }

    private function contingentFor(string $slug, string $role, int $n): ?string
    {
        return match (true) {
            $slug === 'navigamus3027' && $role === 'pl' => self::NAVIGAMUS_CONTINGENTS[$n % 5],
            $slug === 'cej3026' && ($role === 'pl' || $role === 'ist') => self::CEJ_CONTINGENTS[$n % 5],
            default => null,
        };
    }

    private function statusForIndex(int $n): string
    {
        return match (true) {
            $n <= 5 => 'open',
            $n <= 10 => 'closed',
            $n <= 15 => 'approved',
            default => 'paid',
        };
    }

    // the init migration inserts event id 1 explicitly without bumping event_id_seq, so every
    // migrated postgres would hand out id 1 again; only event needs this, and only ever forward -
    // a hard setval on the other tables could reissue ids freed by app-side deletes. sqlite has none
    private function realignIdSequences(): void
    {
        if ($this->getAdapter()->getAdapterType() !== 'pgsql') {
            return;
        }

        $this->execute(
            'SELECT setval(\'event_id_seq\', GREATEST('
            . '(SELECT last_value FROM event_id_seq), (SELECT COALESCE(MAX(id), 0) FROM event)))',
        );
    }

    private function eventExists(string $slug): bool
    {
        return $this->fetchRow(sprintf("SELECT id FROM event WHERE slug = '%s'", $slug)) !== false;
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function insertEvent(array $definition): int
    {
        /** @var string $slug */
        $slug = $definition['slug'];
        /** @var list<string> $roles */
        $roles = $definition['roles'];

        $allowPatrols = in_array('pl', $roles, true);
        $allowTroops = in_array('tl', $roles, true);
        $allowIsts = in_array('ist', $roles, true);
        $allowGuests = in_array('guest', $roles, true);
        $allowOrganizingTeam = in_array('ot', $roles, true);

        $row = [
            'slug' => $slug,
            'readable_name' => $definition['readable_name'],
            'event_type' => $definition['event_type'],
            'logo_url' => $definition['logo_url'],
            'start_day' => $definition['start_day'],
            'end_day' => $definition['end_day'],
            'prefix_variable_symbol' => $definition['prefix_variable_symbol'],
            'web_url' => sprintf('https://%s.test/', $slug),
            'data_protection_url' => sprintf('https://%s.test/gdpr/', $slug),
            'contact_email' => sprintf('info@%s.test', $slug),
            'email_from' => 'registration@kissj.net',
            'email_from_name' => 'Registration Office',
            'account_number' => self::ACCOUNT_NUMBER,
            'iban' => self::IBAN,
            'swift' => self::SWIFT,
            'constant_symbol' => self::CONSTANT_SYMBOL,
            'bank_slug' => '100000000',
            // empty skautisAppId leaves SkautisService uninitialized and 500s the login page
            // of skautis-enabled event types - a dummy id keeps the page rendering, like event 1
            'skautis_app_id' => 'testSkautisAppId',
            'automatic_payment_pairing' => false,
            'currency' => 'Kč',
            'default_price' => 1000,
            'start_registration' => '2026-01-01 00:00:00',
            'created_at' => self::CREATED_AT,
            'updated_at' => self::CREATED_AT,
            'allow_patrols' => $allowPatrols,
            'allow_troops' => $allowTroops,
            'allow_ists' => $allowIsts,
            'allow_guests' => $allowGuests,
            'allow_organizing_team' => $allowOrganizingTeam,
        ];

        if ($allowPatrols) {
            $row['minimal_patrol_participants_count'] = 1;
            $row['maximal_patrol_participants_count'] = 4;
        }
        if ($allowTroops) {
            $row['minimal_troop_participants_count'] = 1;
            $row['maximal_troop_participants_count'] = 4;
        }
        if ($allowOrganizingTeam) {
            $row['organizing_team_registration_token'] = 'orgtoken-' . $slug;
            $row['organizing_team_price'] = 500;
        }
        if ($allowGuests) {
            $row['guest_price'] = 500;
        }

        $this->table('event')->insert($row)->saveData();

        $insertedRow = $this->fetchRow(sprintf("SELECT id FROM event WHERE slug = '%s'", $slug));
        if ($insertedRow === false) {
            throw new RuntimeException('inserted event not found by slug: ' . $slug);
        }

        return $this->rowId($insertedRow);
    }

    private function insertUser(int $eventId, string $email, string $role, string $status): int
    {
        $this->table('user')->insert([
            'email' => $email,
            'status' => $status,
            'role' => $role,
            'login_type' => 'email',
            'event_id' => $eventId,
            'created_at' => self::CREATED_AT,
            'updated_at' => self::CREATED_AT,
        ])->saveData();

        $insertedRow = $this->fetchRow(sprintf(
            'SELECT id FROM "user" WHERE email = \'%s\' AND event_id = %d',
            $email,
            $eventId,
        ));
        if ($insertedRow === false) {
            throw new RuntimeException('inserted user not found by email: ' . $email);
        }

        return $this->rowId($insertedRow);
    }

    private function insertParticipant(
        string $slug,
        string $role,
        int $n,
        ?int $userId,
        ?int $patrolLeaderId,
        ?string $contingent = null,
        // TPs derive status from their leader, not their own index — see insertTroops()
        ?string $statusOverride = null,
    ): int {
        $status = $statusOverride ?? $this->statusForIndex($n);
        [$firstName, $lastName, $gender] = self::NAME_POOL[$n - 1];

        $birthDate = in_array($role, ['pp', 'tp'], true)
            ? sprintf('%d-06-1%d', 2012 + $n % 4, $n % 9)
            : sprintf('%d-03-0%d', 1985 + $n, 1 + $n % 9);

        $row = [
            'user_id' => $userId,
            'patrol_leader_id' => $patrolLeaderId,
            'contingent' => $contingent,
            'role' => $role,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => $gender,
            'birth_date' => $birthDate,
            'permanent_residence' => 'Praha',
            'country' => 'detail.countryCzechRepublic',
            'scout_unit' => 'středisko Kotva',
            'telephone_number' => '+42077700' . sprintf('%04d', $n),
            'email' => sprintf('%s%d@%s.test', $role, $n, $slug),
            'food_preferences' => self::FOOD_PREFERENCES[$n % 3],
            'tie_code' => strtoupper(substr(md5($slug . $role . $n), 0, 6)),
            'entry_code' => Uuid::uuid4()->toString(),
            'admin_note' => '',
            'created_at' => self::CREATED_AT,
            'updated_at' => self::CREATED_AT,
        ];

        // PP has no user, the app never writes its registration dates
        if ($role !== 'pp') {
            if (in_array($status, ['closed', 'approved', 'paid'], true)) {
                $row['registration_close_date'] = '2026-09-01 11:00:00';
            }
            if (in_array($status, ['approved', 'paid'], true)) {
                $row['registration_approve_date'] = '2026-09-01 12:00:00';
            }
            if ($status === 'paid') {
                $row['registration_pay_date'] = '2026-09-01 13:00:00';
            }
        }
        if ($role === 'pl') {
            $row['patrol_name'] = 'Družina ' . $lastName;
        }
        if ($role === 'tl') {
            $row['patrol_name'] = 'Oddíl ' . $lastName;
        }

        $this->table('participant')->insert($row)->saveData();

        $insertedRow = $this->fetchRow(sprintf(
            "SELECT id FROM participant WHERE role = '%s' AND email = '%s'",
            $role,
            $row['email'],
        ));
        if ($insertedRow === false) {
            throw new RuntimeException('inserted participant not found by email: ' . $row['email']);
        }

        return $this->rowId($insertedRow);
    }

    /**
     * @param array<string, mixed> $eventDefinition
     */
    private function insertPaymentIfDue(
        int $participantId,
        string $status,
        string $slug,
        string $role,
        int $n,
        array $eventDefinition,
    ): void {
        $paymentStatus = match ($status) {
            'approved' => 'waiting',
            'paid' => 'paid',
            default => null,
        };
        if ($paymentStatus === null) {
            return;
        }

        $prefixVariableSymbol = $eventDefinition['prefix_variable_symbol'];
        if (!is_int($prefixVariableSymbol)) {
            throw new RuntimeException('unexpected prefix_variable_symbol type: ' . get_debug_type($prefixVariableSymbol));
        }
        $startDay = $eventDefinition['start_day'];
        if (!is_string($startDay)) {
            throw new RuntimeException('unexpected start_day type: ' . get_debug_type($startDay));
        }

        $row = [
            'variable_symbol' => sprintf(
                '%d%d%s',
                $prefixVariableSymbol,
                self::ROLE_ORDINALS[$role],
                str_pad((string)$n, 10 - strlen((string)$prefixVariableSymbol) - 1, '0', STR_PAD_LEFT),
            ),
            'price' => in_array($role, ['guest', 'ot'], true) ? '500' : '1000',
            'currency' => 'Kč',
            'status' => $paymentStatus,
            'purpose' => 'event fee',
            'account_number' => self::ACCOUNT_NUMBER,
            'iban' => self::IBAN,
            'swift' => self::SWIFT,
            'constant_symbol' => self::CONSTANT_SYMBOL,
            'due' => $this->dueDateFor($startDay),
            'note' => $this->composeNote($slug, $role, $n),
            'participant_id' => $participantId,
            'created_at' => self::CREATED_AT,
            'updated_at' => self::CREATED_AT,
        ];
        if ($paymentStatus === 'paid') {
            $row['paid_at'] = '2026-09-01 13:00:00';
        }

        $this->table('payment')->insert($row)->saveData();
    }

    // mirrors PaymentService::calculatePaymentDueDate(): created_at + 14 days, capped at event start
    private function dueDateFor(string $startDay): string
    {
        $createdAtPlus14Days = DateTimeUtils::getDateTime(self::CREATED_AT)->modify('+14 days')->format('Y-m-d') . ' 00:00:00';
        $eventStart = $startDay . ' 00:00:00';

        return min($createdAtPlus14Days, $eventStart);
    }

    // mirrors PaymentService::composeNote()
    private function composeNote(string $slug, string $role, int $n): string
    {
        [$firstName, $lastName] = self::NAME_POOL[$n - 1];

        if ($role === 'pl' || $role === 'tl') {
            $patrolName = ($role === 'pl' ? 'Družina ' : 'Oddíl ') . $lastName;

            return $slug . ' ' . $patrolName . ' ' . $firstName . ' ' . $lastName;
        }

        return $slug . ' ' . $firstName . ' ' . $lastName;
    }

    /**
     * @param array<array-key, mixed> $row
     */
    private function rowId(array $row): int
    {
        $id = $row['id'];
        if (!is_int($id) && !is_string($id)) {
            throw new RuntimeException('unexpected id column type: ' . get_debug_type($id));
        }

        return (int)$id;
    }
}
