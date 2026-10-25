<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class ConvertDatetimesToTimestamptz extends AbstractMigration
{
    private const array COLUMNS = [
        'bankpayment' => ['created_at', 'move_date', 'updated_at'],
        'deal' => ['created_at', 'done_at', 'updated_at'],
        'event' => [
            'created_at',
            'programme_registration_end',
            'programme_registration_start',
            'updated_at',
        ],
        'logintoken' => ['created_at', 'updated_at'],
        'participant' => [
            'created_at',
            'entry_date',
            'leave_date',
            'registration_approve_date',
            'registration_close_date',
            'registration_pay_date',
            'updated_at',
        ],
        'payment' => ['created_at', 'due', 'paid_at', 'updated_at'],
        'programme' => ['created_at', 'deleted_at', 'end', 'start', 'updated_at'],
        'programme_registration' => ['created_at', 'deleted_at', 'updated_at'],
        'user' => ['created_at', 'updated_at'],
    ];

    public function up(): void
    {
        if ($this->isPostgres() === false) {
            return;
        }

        foreach (self::COLUMNS as $table => $columns) {
            $this->alterColumns($table, $columns, 'timestamptz');
        }

        $this->execute(
            'ALTER TABLE "event" ALTER COLUMN "start_registration" TYPE timestamptz'
            . ' USING "start_registration" AT TIME ZONE \'UTC\''
        );

        $this->execute(
            'ALTER TABLE "participant" ALTER COLUMN "birth_date" TYPE date USING "birth_date"::date'
        );
    }

    public function down(): void
    {
        if ($this->isPostgres() === false) {
            return;
        }

        foreach (self::COLUMNS as $table => $columns) {
            $this->alterColumns($table, $columns, 'timestamp');
        }

        $this->execute(
            'ALTER TABLE "event" ALTER COLUMN "start_registration" TYPE timestamp'
            . ' USING "start_registration" AT TIME ZONE \'UTC\''
        );

        $this->execute(
            'ALTER TABLE "participant" ALTER COLUMN "birth_date" TYPE timestamp USING "birth_date"::timestamp'
        );
    }

    private function isPostgres(): bool
    {
        $adapter = $this->getAdapter();
        if ($adapter === null) {
            throw new LogicException('Migration runs without a database adapter');
        }

        return $adapter->getAdapterType() === 'pgsql';
    }

    /**
     * @param list<string> $columns
     */
    private function alterColumns(string $table, array $columns, string $type): void
    {
        $alterations = array_map(
            fn (string $column): string => sprintf(
                'ALTER COLUMN "%1$s" TYPE %2$s USING "%1$s" AT TIME ZONE \'Europe/Berlin\'',
                $column,
                $type,
            ),
            $columns,
        );

        $this->execute(sprintf('ALTER TABLE "%s" %s', $table, implode(', ', $alterations)));
    }
}
