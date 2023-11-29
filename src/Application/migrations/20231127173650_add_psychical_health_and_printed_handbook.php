<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPsychicalHealthAndPrintedHandbook extends AbstractMigration
{
    public function up(): void
    {
        $tableUser = $this->table('participant');
        $tableUser->addColumn('medicaments', 'string', ['null' => true]);
        $tableUser->addColumn('psychical_health_problems', 'string', ['null' => true]);
        $tableUser->addColumn('printed_handbook', 'boolean', ['null' => true]);
        $tableUser->save();
    }

    public function down(): void
    {
        $tableUser = $this->table('participant');
        $tableUser->removeColumn('medicaments');
        $tableUser->removeColumn('psychical_health_problems');
        $tableUser->removeColumn('printed_handbook');
        $tableUser->save();
    }
}
