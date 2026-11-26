<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class RenameProgrammePreregisteredToAutoenrolled extends AbstractMigration
{
    public function up(): void
    {
        $this->table('programme')->renameColumn('is_preregistered', 'is_autoenrolled')->update();
        $this->renamePostgresConstraint('programme_is_preregistered_not_null', 'programme_is_autoenrolled_not_null');
    }

    public function down(): void
    {
        $this->renamePostgresConstraint('programme_is_autoenrolled_not_null', 'programme_is_preregistered_not_null');
        $this->table('programme')->renameColumn('is_autoenrolled', 'is_preregistered')->update();
    }

    private function renamePostgresConstraint(string $from, string $to): void
    {
        if ($this->getAdapter()?->getAdapterType() !== 'pgsql') {
            return;
        }

        $this->execute(sprintf(
            'DO $$ BEGIN IF EXISTS (SELECT 1 FROM pg_constraint'
            . " WHERE conrelid = 'programme'::regclass AND conname = '%s')"
            . ' THEN ALTER TABLE programme RENAME CONSTRAINT %s TO %s; END IF; END $$',
            $from,
            $from,
            $to,
        ));
    }
}
