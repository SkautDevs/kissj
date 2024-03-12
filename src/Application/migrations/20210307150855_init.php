<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Init extends AbstractMigration
{
    public function up(): void
    {
        $bankpayment = $this->table('bankpayment');
        $bankpayment->addColumn('bank_id', 'text', ['null' => true])
			->addColumn('name_account_from', 'text', ['null' => true])
			->addColumn('move_date', 'datetime', ['null' => true])
			->addColumn('price', 'text', ['null' => true])
			->addColumn('variable_symbol', 'text', ['null' => true])
			->addColumn('account_number', 'text', ['null' => true])
			->addColumn('constant_symbol', 'text', ['null' => true])
			->addColumn('specific_symbol', 'text', ['null' => true])
			->addColumn('note', 'text', ['null' => true])
			->addColumn('currency', 'text', ['null' => true])
			->addColumn('message', 'text', ['null' => true])
			->addColumn('advanced_information', 'text', ['null' => true])
			->addColumn('comment', 'text', ['null' => true])
			->addColumn('status', 'text', ['null' => true])
			->addColumn('created_at', 'datetime', ['null' => true])
			->addColumn('updated_at', 'datetime', ['null' => true])
					->create();

        $event = $this->table('event');
        $event->addColumn('slug', 'text', ['null' => true])
			->addColumn('readable_name', 'text', ['null' => true])
			->addColumn('web_url', 'text', ['null' => true])
			->addColumn('data_protection_url', 'text', ['null' => true])
			->addColumn('contact_email', 'text', ['null' => true])
			->addColumn('account_number', 'text', ['null' => true])
			->addColumn('prefix_variable_symbol', 'integer', ['null' => true])
			->addColumn('automatic_payment_pairing', 'boolean', ['null' => true])
			->addColumn('bank_id', 'integer', ['null' => true])
			->addColumn('bank_api_key', 'text', ['null' => true])
			->addColumn('max_elapsed_payment_days', 'integer', ['null' => true])
			->addColumn('currency', 'text', ['null' => true])
			->addColumn('allow_patrols', 'boolean', ['null' => true])
			->addColumn('maximal_closed_patrols_count', 'integer', ['null' => true])
			->addColumn('minimal_patrol_participants_count', 'integer', ['null' => true])
			->addColumn('maximal_patrol_participants_count', 'integer', ['null' => true])
			->addColumn('allow_ists', 'boolean', ['null' => true])
			->addColumn('maximal_closed_ists_count', 'integer', ['null' => true])
			->addColumn('allow_guests', 'boolean', ['null' => true])
			->addColumn('maximal_closed_guests_count', 'integer', ['null' => true])
			->addColumn('start_day', 'date', ['null' => true])
			->addColumn('end_day', 'date', ['null' => true])
			->addColumn('created_at', 'datetime', ['null' => true])
			->addColumn('updated_at', 'datetime', ['null' => true])
			->create();

        $user = $this->table('user');
        $user->addColumn('email', 'text')
			->addColumn('status', 'text')
			->addColumn('created_at', 'datetime')
			->addColumn('updated_at', 'datetime')
			->addColumn('event_id', 'integer', ['default' => '1'])
			->addForeignKey('event_id', 'event', ['id'], ['constraint' => 'user_event_id_fk'])
			->addColumn('role', 'text', ['default' => 'withoutRole'])
			->addIndex(['email'], ['unique' => true, 'name' => 'user_email_uindex'])
			->create();

        $logintoken = $this->table('logintoken');
        $logintoken->addColumn('token', 'text')
			->addColumn('user_id', 'integer')
			->addForeignKey('user_id', 'user', ['id'], ['constraint' => 'login_tokens_users_id_fk'])
			->addColumn('used', 'boolean')
			->addColumn('created_at', 'datetime')
			->addColumn('updated_at', 'datetime')
			->create();

        $participant = $this->table('participant');
        $participant->addColumn('user_id', 'integer', ['null' => true])
			->addForeignKey('user_id', 'user', ['id'], ['constraint' => 'ist_userId_fk'])
			->addColumn('first_name', 'text', ['null' => true])
			->addColumn('last_name', 'text', ['null' => true])
			->addColumn('nickname', 'text', ['null' => true])
			->addColumn('gender', 'text', ['null' => true])
			->addColumn('birth_date', 'datetime', ['null' => true])
			->addColumn('birth_place', 'text', ['null' => true])
			->addColumn('permanent_residence', 'text', ['null' => true])
			->addColumn('country', 'text', ['null' => true])
			->addColumn('id_number', 'text', ['null' => true])
			->addColumn('telephone_number', 'text', ['null' => true])
			->addColumn('email', 'text', ['null' => true])
			->addColumn('legal_representative', 'text', ['null' => true])
			->addColumn('health_problems', 'text', ['null' => true])
			->addColumn('food_preferences', 'text', ['null' => true])
			->addColumn('scout_unit', 'text', ['null' => true])
			->addColumn('tshirt', 'text', ['null' => true])
			->addColumn('scarf', 'text', ['null' => true])
			->addColumn('notes', 'text', ['null' => true])
			->addColumn('created_at', 'datetime', ['null' => true])
			->addColumn('updated_at', 'datetime', ['null' => true])
			->addColumn('patrol_leader_id', 'integer', ['null' => true])
			->addColumn('patrol_name', 'text', ['null' => true])
			->addColumn('drivers_license', 'text', ['null' => true])
			->addColumn('languages', 'text', ['null' => true])
			->addColumn('skills', 'text', ['null' => true])
			->addColumn('preferred_position', 'text', ['null' => true])
			->addColumn('role', 'text', ['null' => true])
			->addColumn('swimming', 'text', ['null' => true])
			->addColumn('arrival_date', 'text', ['null' => true])
			->addColumn('departue_date', 'text', ['null' => true])
			->addColumn('uploaded_filename', 'text', ['null' => true])
			->addColumn('uploaded_original_filename', 'text', ['null' => true])
			->addColumn('uploaded_contenttype', 'text', ['null' => true])
			->create();

        $payment = $this->table('payment');
        $payment->addColumn('variable_symbol', 'text')
			->addColumn('price', 'text')
			->addColumn('currency', 'text')
			->addColumn('status', 'text')
			->addColumn('purpose', 'text')
			->addColumn('account_number', 'text')
			->addColumn('created_at', 'datetime')
			->addColumn('updated_at', 'datetime')
			->addColumn('participant_id', 'integer')
			->addForeignKey('participant_id', 'participant')
			->addColumn('note', 'text')
			->create();
        #initialise data
        $default_event = [
			'id' => 1,
			'slug' => 'test-event-slug',
			'readable_name' => 'test-event-readable-name',
			'web_url' => 'https://test.example.com/',
			'data_protection_url' => 'https://test.example.com/data-protection/',
			'contact_email' => 'test-contact@example.com',
			'account_number' => '',
			'prefix_variable_symbol' => '42',
			'automatic_payment_pairing' => false,
			'bank_id' => 100000000,
			'bank_api_key' => 'readTokenFromFioIBanking',
			'max_elapsed_payment_days' => 7,
			'currency' => 'Kč',
			'allow_patrols' => true,
			'maximal_closed_patrols_count' => 10,
			'minimal_patrol_participants_count' => 2,
			'maximal_patrol_participants_count' => 3,
			'allow_ists' => true,
			'maximal_closed_ists_count' => 10,
			'allow_guests' => true,
			'maximal_closed_guests_count' => 20,
			'start_day' => '2021-01-01',
			'end_day' => '2021-01-05',
			'created_at' => '2021-01-01',
			'updated_at'	 => '2021-01-01'
		];
        $event->insert($default_event)->save();
    }

    public function down(): void
    {
        $bankpayment = $this->table('bankpayment');
        $event = $this->table('event');
        $user = $this->table('user');
        $logintoken = $this->table('logintoken');
        $participant = $this->table('participant');
        $payment = $this->table('payment');

        $event->drop();
        $bankpayment ->drop();
        $user->drop();
        $logintoken->drop();
        $participant->drop();
        $payment->drop();
    }
}
