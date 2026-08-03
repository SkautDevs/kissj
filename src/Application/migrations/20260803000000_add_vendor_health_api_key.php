<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddVendorHealthApiKey extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('event');
        $table->addColumn('api_key_vendor_health', 'string', ['null' => true, 'default' => null]);
        $table->save();
    }

    public function down(): void
    {
        $table = $this->table('event');
        $table->removeColumn('api_key_vendor_health');
        $table->save();
    }
}
