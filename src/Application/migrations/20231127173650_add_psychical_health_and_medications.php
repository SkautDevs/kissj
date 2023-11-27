<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPsychicalHealthAndMedications extends AbstractMigration
{
    public function up(): void
    {
        $tableUser = $this->table('participant');
        $tableUser->addColumn('medications', 'string', ['null' => true]);
        $tableUser->addColumn('psychical_health_problems', 'string', ['null' => true]);
        $tableUser->save();
    }

    public function down(): void
    {
        $tableUser = $this->table('participant');
        $tableUser->removeColumn('medications');
        $tableUser->removeColumn('psychical_health_problems');
        $tableUser->save();
    }
}
