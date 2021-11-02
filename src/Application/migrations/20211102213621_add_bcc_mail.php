<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddBccMail extends AbstractMigration
{
    public function up(): void
    {
        $event = $this->table('event');
        $event
            ->addColumn('email_from_bcc', 'text', ['null' => true])
            ->save();
    }

    public function down(): void
    {
        $event = $this->table('event');
        $event
            ->removeColumn('email_from_bcc')
            ->save();
    }
}
