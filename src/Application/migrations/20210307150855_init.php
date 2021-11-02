<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Init extends AbstractMigration {
    public function up(): void
	{
		$bankpayment = $this->table('bankpayment');
		$bankpayment->addColumn('bank_id','text')
					->addColumn('name_account_from','text')
					->addColumn('move_date','datetime')
					->addColumn('price','text')
					->addColumn('variable_symbol','text')
					->addColumn('account_number','text')
					->addColumn('constant_symbol','text')
					->addColumn('specific_symbol','text')
					->addColumn('note','text')
					->addColumn('currency','text')
					->addColumn('message','text')
					->addColumn('advanced_information','text')
					->addColumn('comment','text')
					->addColumn('status','text')
					->addColumn('created_at','datetime')
					->addColumn('updated_at','datetime')
					->create();

		$event = $this->table('event');
		$event->addColumn('slug','text')
			->addColumn('readable_name','text')
			->addColumn('web_url','text')
			->addColumn('data_protection_url','text')
			->addColumn('contact_email','text')
			->addColumn('account_number','text')
			->addColumn('prefix_variable_symbol','integer')
			->addColumn('automatic_payment_pairing','boolean')
			->addColumn('bank_id','integer')
			->addColumn('bank_api_key','text')
			->addColumn('max_elapsed_payment_days','integer')
			->addColumn('currency','text')
			->addColumn('allow_patrols','boolean')
			->addColumn('maximal_closed_patrols_count','integer')
			->addColumn('minimal_patrol_participants_count','integer')
			->addColumn('maximal_patrol_participants_count','integer')
			->addColumn('allow_ists','boolean')
			->addColumn('maximal_closed_ists_count','integer')
			->addColumn('allow_guests','boolean')
			->addColumn('maximal_closed_guests_count','integer')
			->addColumn('start_day','date')
			->addColumn('end_day','date')
			->addColumn('created_at','datetime')
			->addColumn('updated_at','datetime')
			->create();

		$user = $this->table('user');
		$user->addColumn('email', 'text')
			->addColumn('status','text')
			->addColumn('created_at', 'datetime')
			->addColumn('event_id', 'integer', ['default' => '1'])
			->addForeignKey('event_id', 'event', ['id'], ['constraint' => 'user_event_id_fk'])
			->addColumn('role', 'text', ['default' => 'withoutRole'])
			->addIndex(['email'], ['unique' => true, 'name' => 'user_email_uindex'])
			->create();

		$logintoken = $this->table('logintoken');
		$logintoken->addColumn('token', 'text')
			->addColumn('user_id', 'integer')
			->addForeignKey('user_id', 'user', ['id'], ['constraint' => 'login_tokens_users_id_fk'])
			->addColumn('used','boolean')
			->addColumn('created_at','datetime')
			->addColumn('updated_at','datetime')
			->create();

		$participant= $this->table('participant');
		$participant->addColumn('user_id', 'integer')
			->addForeignKey('user_id', 'user', ['id'], ['constraint' => 'ist_userId_fk'])
			->addColumn('first_name', 'text')
			->addColumn('last_name', 'text')
			->addColumn('nickname', 'text')
			->addColumn('gender', 'text')
			->addColumn('birth_date', 'datetime')
			->addColumn('birth_place', 'datetime')
			->addColumn('permanent_residence', 'text')
			->addColumn('country', 'text')
			->addColumn('id_number', 'text')
			->addColumn('telephone_number', 'text')
			->addColumn('email', 'text')
			->addColumn('legal_representative', 'text')
			->addColumn('health_problems', 'text')
			->addColumn('food_preferences', 'text')
			->addColumn('scout_unit', 'text')
			->addColumn('tshirt', 'text')
			->addColumn('scarf', 'text')
			->addColumn('notes', 'text')
			->addColumn('created_at', 'datetime')
			->addColumn('updated_at', 'datetime')
			->addColumn('patrol_leader_id', 'integer')
			->addColumn('patrol_name', 'text')
			->addColumn('drivers_license', 'text')
			->addColumn('languages', 'text')
			->addColumn('skills', 'text')
			->addColumn('preferred_position', 'text')
			->addColumn('role', 'text')
			->addColumn('swimming', 'text')
			->addColumn('arrival_date', 'text')
			->addColumn('departue_date', 'text')
			->addColumn('uploaded_filename', 'text')
			->addColumn('uploaded_original_filename','text')
			->addColumn('uploaded_contenttype', 'text')
			->create();

		$payment = $this->table('payment');
		$payment->addColumn('variable_symbol','text')
				->addColumn('price','text')
				->addColumn('currency','text')
				->addColumn('status','text')
				->addColumn('purpose','text')
				->addColumn('account_number','text')
				->addColumn('created_at','datetime')
				->addColumn('updated_at','datetime')
				->addColumn('participant_id','integer')
				->addForeignKey('participant_id', 'participant', ['id'])
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

    public function down(): void {

		$bankpayment = $this->table('bankpayment');
		$event = $this->table('event');
		$user = $this->table('user');
		$logintoken = $this->table('logintoken');
		$participant= $this->table('participant');
		$payment = $this->table('payment');

		$event->drop();
		$bankpayment ->drop();
		$user->drop();
		$logintoken->drop();
		$participant->drop();
		$payment->drop();
	}
}

