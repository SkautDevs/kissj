<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CascadeProgrammeRegistrationParticipant extends AbstractMigration
{
    public function up(): void
    {
        $this->table('programme_registration')
            ->dropForeignKey('participant_id')
            ->addForeignKey('participant_id', 'participant', ['id'], [
                'constraint' => 'programme_registration_participant_id_fk',
                'delete' => 'CASCADE',
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('programme_registration')
            ->dropForeignKey('participant_id')
            ->addForeignKey('participant_id', 'participant', ['id'], [
                'constraint' => 'programme_registration_participant_id_fk',
            ])
            ->update();
    }
}
