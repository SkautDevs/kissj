<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddEventToBankPayment extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('bankpayment');
        $event
            ->addColumn('event_id', 'integer', ['null' => false])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('bankpayment');
        $event->removeColumn('event_id')->save();
    }
}
