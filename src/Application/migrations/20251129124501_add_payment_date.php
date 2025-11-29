<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPaymentDate extends AbstractMigration
{
    public function up(): void
    {
        $tablePayment = $this->table('payment');
        $tablePayment->addColumn('paid_at', 'datetime', ['null' => true]);
        $tablePayment->save();
    }

    public function down(): void
    {
        $participantTable = $this->table('payment');
        $participantTable->removeColumn('paid_at');
        $participantTable->save();
    }
}
