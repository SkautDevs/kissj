<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPerformanceIndexes extends AbstractMigration
{
    public function up(): void
    {
        $participant = $this->table('participant');
        $participant->addIndex('role', ['name' => 'idx_participant_role'])
                   ->addIndex('patrol_leader_id', ['name' => 'idx_participant_patrol_leader_id'])
                   ->addIndex(['entry_date', 'leave_date'], ['name' => 'idx_participant_entry_leave'])
                   ->addIndex('contingent', ['name' => 'idx_participant_contingent'])
                   ->addIndex(['user_id', 'role'], ['name' => 'idx_participant_user_role'])
                   ->save();

        $user = $this->table('user');
        $user->addIndex('status', ['name' => 'idx_user_status'])
             ->addIndex('role', ['name' => 'idx_user_role'])
             ->addIndex(['event_id', 'status'], ['name' => 'idx_user_event_status'])
             ->save();

        $payment = $this->table('payment');
        $payment->addIndex('status', ['name' => 'idx_payment_status'])
                ->addIndex('variable_symbol', ['name' => 'idx_payment_variable_symbol'])
                ->addIndex(['participant_id', 'status'], ['name' => 'idx_payment_participant_status'])
                ->save();

        $bankPayment = $this->table('bankpayment');
        $bankPayment->addIndex('variable_symbol', ['name' => 'idx_bankpayment_variable_symbol'])
                   ->addIndex(['event_id', 'status'], ['name' => 'idx_bankpayment_event_status'])
                   ->save();
    }

    public function down(): void
    {
        $participant = $this->table('participant');
        $participant->removeIndexByName('idx_participant_role')
                   ->removeIndexByName('idx_participant_patrol_leader_id')
                   ->removeIndexByName('idx_participant_entry_leave')
                   ->removeIndexByName('idx_participant_contingent')
                   ->removeIndexByName('idx_participant_user_role')
                   ->save();

        $user = $this->table('user');
        $user->removeIndexByName('idx_user_status')
             ->removeIndexByName('idx_user_role')
             ->removeIndexByName('idx_user_event_status')
             ->save();

        $payment = $this->table('payment');
        $payment->removeIndexByName('idx_payment_status')
                ->removeIndexByName('idx_payment_variable_symbol')
                ->removeIndexByName('idx_payment_participant_status')
                ->save();

        $bankPayment = $this->table('bankpayment');
        $bankPayment->removeIndexByName('idx_bankpayment_variable_symbol')
                   ->removeIndexByName('idx_bankpayment_event_status')
                   ->save();
    }
}
