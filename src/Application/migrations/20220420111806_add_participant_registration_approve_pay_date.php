<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddParticipantRegistrationApprovePayDate extends AbstractMigration
{
    public function up(): void
    {
        $participant = $this->table('participant');
        $participant
            ->addColumn('registration_approve_date', 'datetime', ['default' => null, 'null' => true])
            ->addColumn('registration_pay_date', 'datetime', ['default' => null, 'null' => true])
            ->save();

        $this->execute('
            UPDATE participant
            SET participant.registration_approve_date = participant.updated_at
            FROM user
            WHERE user.id = participant.user_id
            AND user.status IN (\'approved\', \'paid\');
        ');

        $this->execute('
            UPDATE participant
            SET participant.registration_pay_date = participant.updated_at
            FROM user
            WHERE user.id = participant.user_id
            AND user.status IN (\'paid\');
        ');
    }

    public function down(): void
    {
        $participant = $this->table('participant');
        $participant
            ->removeColumn('registration_approve_date')
            ->removeColumn('registration_pay_date')
            ->save();
    }
}
