<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RefactorBanks extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('event');
        $table->renameColumn('bank_id', 'bank_slug');
        $table->changeColumn('bank_slug', 'string', ['null' => false]);
        $table->save();
    }

    public function down(): void
    {
        $table = $this->table('event');
        $table->renameColumn('bank_slug', 'bank_id');
        $table->changeColumn('bank_id', 'string', ['null' => false]); // change to string 4ever, int was just a terrible mistake, let's forget about it 
        $table->save();
    }
}
