<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPerformanceIndexes extends AbstractMigration
{
    public function up(): void
    {
        // Add indexes for participant queries
        $participant = $this->table('participant');
        $participant->addIndex('role', ['name' => 'idx_participant_role'])
                   ->addIndex('patrol_leader_id', ['name' => 'idx_participant_patrol_leader_id'])
                   ->addIndex('user_id', ['name' => 'idx_participant_user_id'])
                   ->addIndex(['entry_date', 'leave_date'], ['name' => 'idx_participant_entry_leave'])
                   ->addIndex('contingent', ['name' => 'idx_participant_contingent'])
                   ->addIndex(['user_id', 'role'], ['name' => 'idx_participant_user_status'])
                   ->save();
        
        // Add indexes for user queries
        $user = $this->table('user');
        $user->addIndex('status', ['name' => 'idx_user_status'])
             ->addIndex('event_id', ['name' => 'idx_user_event_id'])
             ->addIndex('role', ['name' => 'idx_user_role'])
             ->addIndex(['event_id', 'status'], ['name' => 'idx_user_event_status'])
             ->save();
        
        // Add indexes for payment queries
        $payment = $this->table('payment');
        $payment->addIndex('status', ['name' => 'idx_payment_status'])
                ->addIndex('participant_id', ['name' => 'idx_payment_participant_id'])
                ->addIndex('variable_symbol', ['name' => 'idx_payment_variable_symbol'])
                ->addIndex(['participant_id', 'status'], ['name' => 'idx_payment_participant_status'])
                ->save();
        
        // Add indexes for bank payment queries
        $bankPayment = $this->table('bankpayment');
        $bankPayment->addIndex('status', ['name' => 'idx_bankpayment_status'])
                   ->addIndex('variable_symbol', ['name' => 'idx_bankpayment_variable_symbol'])
                   ->save();
    }

    public function down(): void
    {
        // Remove indexes for participant queries
        $participant = $this->table('participant');
        $participant->removeIndexByName('idx_participant_role')
                   ->removeIndexByName('idx_participant_patrol_leader_id')
                   ->removeIndexByName('idx_participant_user_id')
                   ->removeIndexByName('idx_participant_entry_leave')
                   ->removeIndexByName('idx_participant_contingent')
                   ->removeIndexByName('idx_participant_user_status')
                   ->save();
        
        // Remove indexes for user queries
        $user = $this->table('user');
        $user->removeIndexByName('idx_user_status')
             ->removeIndexByName('idx_user_event_id')
             ->removeIndexByName('idx_user_role')
             ->removeIndexByName('idx_user_event_status')
             ->save();
        
        // Remove indexes for payment queries
        $payment = $this->table('payment');
        $payment->removeIndexByName('idx_payment_status')
                ->removeIndexByName('idx_payment_participant_id')
                ->removeIndexByName('idx_payment_variable_symbol')
                ->removeIndexByName('idx_payment_participant_status')
                ->save();
        
        // Remove indexes for bank payment queries
        $bankPayment = $this->table('bankpayment');
        $bankPayment->removeIndexByName('idx_bankpayment_status')
                   ->removeIndexByName('idx_bankpayment_variable_symbol')
                   ->save();
    }
} 