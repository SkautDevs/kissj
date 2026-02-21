<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ReplaceApiSecretWithScopedKeys extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('event');
        $table->addColumn('api_key_deals', 'string', ['null' => true, 'default' => null]);
        $table->addColumn('api_key_entry', 'string', ['null' => true, 'default' => null]);
        $table->addColumn('api_key_vendor', 'string', ['null' => true, 'default' => null]);
        $table->save();

        $this->execute("UPDATE event SET api_key_deals = api_secret WHERE api_secret != ''");
        $this->execute("UPDATE event SET api_key_entry = api_secret WHERE api_secret != ''");
        $this->execute("UPDATE event SET api_key_vendor = api_secret WHERE api_secret != ''");

        $table->removeColumn('api_secret');
        $table->save();
    }

    public function down(): void
    {
        $table = $this->table('event');
        $table->addColumn('api_secret', 'string', ['null' => false, 'default' => '']);
        $table->save();

        $this->execute("UPDATE event SET api_secret = COALESCE(api_key_entry, api_key_deals, api_key_vendor, '')");

        $table->removeColumn('api_key_deals');
        $table->removeColumn('api_key_entry');
        $table->removeColumn('api_key_vendor');
        $table->save();
    }
}
