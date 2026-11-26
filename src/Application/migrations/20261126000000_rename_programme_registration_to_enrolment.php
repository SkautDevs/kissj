<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class RenameProgrammeRegistrationToEnrolment extends AbstractMigration
{
    private const array INDEX_SUFFIXES = ['programme_idx', 'participant_idx', 'active_uidx'];

    public function up(): void
    {
        $this->table('programme_registration')->rename('programme_enrolment')->update();
        $this->table('event')
            ->renameColumn('programme_registration_start', 'programme_enrolment_start')
            ->renameColumn('programme_registration_end', 'programme_enrolment_end')
            ->update();

        if ($this->isPostgres()) {
            $this->renamePostgresObjects('programme_registration', 'programme_enrolment');
        } else {
            $this->recreateSqliteIndexes('programme_registration', 'programme_enrolment');
        }
    }

    public function down(): void
    {
        if ($this->isPostgres()) {
            $this->renamePostgresObjects('programme_enrolment', 'programme_registration');
        } else {
            $this->recreateSqliteIndexes('programme_enrolment', 'programme_registration');
        }

        $this->table('event')
            ->renameColumn('programme_enrolment_start', 'programme_registration_start')
            ->renameColumn('programme_enrolment_end', 'programme_registration_end')
            ->update();
        $this->table('programme_enrolment')->rename('programme_registration')->update();
    }

    private function isPostgres(): bool
    {
        return $this->getAdapter()?->getAdapterType() === 'pgsql';
    }

    private function renamePostgresObjects(string $from, string $to): void
    {
        $fromPrefix = $from . '_';
        $this->execute(sprintf(
            'DO $$ DECLARE constraint_row record; BEGIN'
            . ' FOR constraint_row IN SELECT conname FROM pg_constraint'
            . " WHERE conrelid = 'programme_enrolment'::regclass AND left(conname, %d) = '%s'"
            . " LOOP EXECUTE format('ALTER TABLE programme_enrolment RENAME CONSTRAINT %%I TO %%I',"
            . " constraint_row.conname, '%s' || substr(constraint_row.conname, %d));"
            . ' END LOOP; END $$',
            strlen($fromPrefix),
            $fromPrefix,
            $to . '_',
            strlen($fromPrefix) + 1,
        ));

        foreach (self::INDEX_SUFFIXES as $suffix) {
            $this->execute('ALTER INDEX ' . $from . '_' . $suffix . ' RENAME TO ' . $to . '_' . $suffix);
        }

        $this->execute('ALTER SEQUENCE ' . $from . '_id_seq RENAME TO ' . $to . '_id_seq');
    }

    private function recreateSqliteIndexes(string $from, string $to): void
    {
        foreach (self::INDEX_SUFFIXES as $suffix) {
            $this->execute('DROP INDEX ' . $from . '_' . $suffix);
        }

        $this->execute('CREATE INDEX ' . $to . '_programme_idx ON programme_enrolment (programme_id)');
        $this->execute('CREATE INDEX ' . $to . '_participant_idx ON programme_enrolment (participant_id)');
        $this->execute(
            'CREATE UNIQUE INDEX ' . $to . '_active_uidx ON programme_enrolment (programme_id, participant_id)'
            . ' WHERE deleted_at IS NULL'
        );
    }
}
