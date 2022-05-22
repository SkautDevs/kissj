<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class FixedPaymentsDue extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('payment');
        $event
            ->changeColumn('iban', 'string', ['null' => false, 'default' => '',])
            ->save();
    }

    public function down(): void
    {
    }
}
