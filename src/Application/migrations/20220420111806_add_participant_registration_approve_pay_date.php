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
            UPDATE public.participant
            SET public.participant.registration_approve_date = participant.updated_at
            FROM public.user
            WHERE public.user.id = public.participant.user_id
            AND public.user.status IN (\'approved\', \'paid\');
        ');

        $this->execute('
            UPDATE public.participant
            SET participant.registration_pay_date = participant.updated_at
            FROM public.user
            WHERE public.user.id = public.participant.user_id
            AND public.user.status IN (\'paid\');
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
