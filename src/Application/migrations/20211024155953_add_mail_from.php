<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMailFrom extends AbstractMigration
{
    public function up(): void
	{
		$event = $this->table('event');
		$event->addColumn('email_from', 'text', ['default' => 'example@example.com'])
			->addColumn('email_from_name', 'text', ['default' => 'Registration Office'])
			->save();
	}
    public function down(): void
    {
		$event = $this->table('event');
		$event->removeColumn('email_from')
			->removeColumn('email_from_name')
			->save();
	}
}
