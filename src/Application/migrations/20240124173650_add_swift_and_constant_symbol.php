<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSwiftAndConstantSymbol extends AbstractMigration
{
    public function up(): void
    {
        $tableUser = $this->table('payment');
        $tableUser->addColumn('swift', 'string', ['null' => false, 'default' => '']);
        $tableUser->addColumn('constant_symbol', 'string', ['null' => false, 'default' => '']);
        $tableUser->save();
    }

    public function down(): void
    {
        $tableUser = $this->table('payment');
        $tableUser->removeColumn('swift');
        $tableUser->removeColumn('constant_symbol');
        $tableUser->save();
    }
}
