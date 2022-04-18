<?php
declare(strict_types=1);

use DateTimeImmutable;
use Phinx\Migration\AbstractMigration;

final class AddParticipantRegistrationCloseDate extends AbstractMigration
{
    public function up(): void
    {
        $participant = $this->table('participant');
        $participant
            ->addColumn('registration_close_date', 'datetime', ['default' => null ,null => true])
            ->save();

        $this->execute('UPDATE participant SET registration_close_date = ' . new DateTimeImmutable() . ' WHERE ...');
    }

    public function down(): void
    {
        $participant = $this->table('participant');
        $participant
            ->removeColumn('registration_close_date')
            ->save();
    }
}
