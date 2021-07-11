<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Init extends AbstractMigration {
    public function up(): void {
        $queries = file_get_contents(__DIR__.'/../../../sql/init.sql');
        if ($queries === false) {
            throw new RuntimeException('cannot load file sql/init.sql');
        }
        
        $this->query($queries);
    }

    public function down(): void {
        
    }
}
