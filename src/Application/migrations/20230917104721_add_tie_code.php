<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTieCode extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('participant');
        $table->addColumn('tie_code', 'string', ['null' => false, 'default' => '123456',]);
        $table->save();
        
        $table->changeColumn('tie_code', 'string', ['null' => false]);
        $table->save();
    }

    public function down(): void
    {
        $table = $this->table('participant');
        $table->removeColumn('tie_code')->save();
    }
}
