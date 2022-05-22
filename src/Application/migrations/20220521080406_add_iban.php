<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIban extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('iban', 'string', ['null' => false])
            ->save();

        $payment = $this->table('payment');
        $payment
            ->addColumn('iban', 'string', ['null' => false, 'default' => '',])
            ->addColumn('due', 'datetime', ['default' => 'NOW()', 'null' => false])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event
            ->removeColumn('iban')
            ->save();

        $payment = $this->table('payment');
        $payment
            ->removeColumn('iban')
            ->removeColumn('due')
            ->save();
    }
}
