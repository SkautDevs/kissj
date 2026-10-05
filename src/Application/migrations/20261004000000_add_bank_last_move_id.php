<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddBankLastMoveId extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('event');
        $table->addColumn('bank_last_move_id', 'biginteger', ['null' => true, 'default' => null]);
        $table->save();
    }

    public function down(): void
    {
        $table = $this->table('event');
        $table->removeColumn('bank_last_move_id');
        $table->save();
    }
}
