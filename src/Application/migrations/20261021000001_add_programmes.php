<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddProgrammes extends AbstractMigration
{
    public function up(): void
    {
        $programme = $this->table('programme');
        $programme->addColumn('name', 'text', ['null' => false]);
        $programme->addColumn('description', 'text', ['null' => false]);
        $programme->addColumn('place', 'text', ['null' => false]);
        $programme->addColumn('image_filename', 'text', ['null' => true]);
        $programme->addColumn('start', 'datetime', ['null' => false]);
        $programme->addColumn('end', 'datetime', ['null' => false]);
        $programme->addColumn('capacity', 'integer', ['null' => false]);
        $programme->addColumn('is_preregistered', 'boolean', ['null' => false, 'default' => false]);
        $programme->addColumn('exclusive_group_name', 'text', ['null' => true]);
        $programme->addColumn('roles', 'text', ['null' => false]);
        $programme->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null]);
        $programme->addColumn('event_id', 'integer', ['null' => false]);
        $programme->addForeignKey('event_id', 'event', ['id'], ['constraint' => 'programme_event_id_fk']);
        $programme->addColumn('created_at', 'datetime', ['null' => false]);
        $programme->addColumn('updated_at', 'datetime', ['null' => false]);
        $programme->create();

        $registration = $this->table('programme_registration');
        $registration->addColumn('programme_id', 'integer', ['null' => false]);
        $registration->addForeignKey('programme_id', 'programme', ['id'], ['constraint' => 'programme_registration_programme_id_fk']);
        $registration->addColumn('participant_id', 'integer', ['null' => false]);
        $registration->addForeignKey('participant_id', 'participant', ['id'], ['constraint' => 'programme_registration_participant_id_fk']);
        $registration->addColumn('deleted_at', 'datetime', ['null' => true, 'default' => null]);
        $registration->addColumn('created_at', 'datetime', ['null' => false]);
        $registration->addColumn('updated_at', 'datetime', ['null' => false]);
        $registration->addIndex(['programme_id'], ['name' => 'programme_registration_programme_idx']);
        $registration->addIndex(['participant_id'], ['name' => 'programme_registration_participant_idx']);
        $registration->create();

        $this->execute(
            'CREATE UNIQUE INDEX programme_registration_active_uidx'
            . ' ON programme_registration (programme_id, participant_id)'
            . ' WHERE deleted_at IS NULL'
        );
    }

    public function down(): void
    {
        $this->table('programme_registration')->drop()->save();
        $this->table('programme')->drop()->save();
    }
}
