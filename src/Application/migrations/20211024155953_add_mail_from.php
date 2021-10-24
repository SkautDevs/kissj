<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMailFrom extends AbstractMigration
{
    public function up(): void
    {
        $this->query('ALTER TABLE `event`
	    ADD `email_from` TEXT NOT NULL DEFAULT "example@example.com";');

        $this->query('ALTER TABLE `event`
	    ADD `email_from_name` TEXT NOT NULL DEFAULT "Registration Ofiice";');
    }

    public function down(): void
    {
        $this->query('
        create table `event_dg_tmp`
            (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                slug TEXT,
                readable_name TEXT,
                web_url TEXT,
                data_protection_url INT,
                contact_email TEXT,
                account_number TEXT,
                prefix_variable_symbol INTEGER,
                automatic_payment_pairing INTEGER,
                bank_id INTEGER,
                bank_api_key TEXT,
                max_elapsed_payment_days INTEGER,
                currency TEXT,
                allow_patrols INTEGER,
                maximal_closed_patrols_count INTEGER,
                minimal_patrol_participants_count INTEGER,
                maximal_patrol_participants_count INTEGER,
                allow_ists INTEGER,
                maximal_closed_ists_count INTEGER,
                allow_guests INTEGER,
                maximal_closed_guests_count INTEGER,
                start_day DATE,
                end_day DATE,
                created_at DATETIME,
                updated_at DATETIME
            );
');
        $this->query('INSERT INTO `event_dg_tmp`(id, slug, readable_name, web_url, data_protection_url, contact_email, account_number, prefix_variable_symbol, automatic_payment_pairing, bank_id, bank_api_key, max_elapsed_payment_days, currency, allow_patrols, maximal_closed_patrols_count, minimal_patrol_participants_count, maximal_patrol_participants_count, allow_ists, maximal_closed_ists_count, allow_guests, maximal_closed_guests_count, start_day, end_day, created_at, updated_at) select id, slug, readable_name, web_url, data_protection_url, contact_email, account_number, prefix_variable_symbol, automatic_payment_pairing, bank_id, bank_api_key, max_elapsed_payment_days, currency, allow_patrols, maximal_closed_patrols_count, minimal_patrol_participants_count, maximal_patrol_participants_count, allow_ists, maximal_closed_ists_count, allow_guests, maximal_closed_guests_count, start_day, end_day, created_at, updated_at FROM `event`;');
        $this->query('DROP TABLE `event`;');
        $this->query('ALTER TABLE `event_dg_tmp` RENAME TO `event`;');
    }
}
