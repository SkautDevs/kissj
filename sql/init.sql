create table bankpayment
(
	id INTEGER
		constraint table_name_pk
		primary key,
	bank_id TEXT,
	name_account_from TEXT,
	move_date DATETIME,
	price TEXT,
	variable_symbol TEXT,
	account_number TEXT,
	constant_symbol TEXT,
	specific_symbol TEXT,
	note TEXT,
	currency TEXT,
	message TEXT,
	advanced_information TEXT,
	comment TEXT,
	status TEXT,
	created_at DATETIME,
	updated_at DATETIME
);

create table event
(
	id INTEGER
		primary key autoincrement,
	slug TEXT,
	readable_name TEXT,
	account_number TEXT,
	prefix_variable_symbol INTEGER,
	automatic_payment_pairing INTEGER,
	bank_id INTEGER,
	bank_api_key TEXT,
	max_elapsed_payment_days INTEGER,
	scarf_price INTEGER,
	tshirt_price INTEGER,
	allow_patrols INTEGER,
	maximal_closed_patrols_count INTEGER,
	minimal_patrol_participants_count INTEGER,
	maximal_patrol_participants_count INTEGER,
	allow_ists INTEGER,
	maximal_closed_ists_count INTEGER,
	web_url TEXT,
	created_at DATETIME,
	updated_at DATETIME,
	data_protection_url int,
	diet_price INTEGER,
	ist_label TEXT,
	event_start DATE,
	contact_email TEXT
);

create table user
(
	id INTEGER
		primary key autoincrement,
	email TEXT not null,
	status TEXT,
	created_at DATETIME not null,
	updated_at DATETIME not null,
	event_id int default 1
		constraint user_event_id_fk
		references event,
	role TEXT default 'withoutRole'
);

create table logintoken
(
	id INTEGER
		primary key autoincrement,
	token TEXT not null,
	user_id INT
		constraint login_tokens_users_id_fk
		references user,
	used BOOLEAN,
	created_at DATETIME not null,
	updated_at DATETIME not null
);

create table participant
(
	id INTEGER
		primary key autoincrement,
	user_id INT
		constraint ist_userId_fk
		references user,
	first_name TEXT,
	last_name TEXT,
	nickname TEXT,
	gender TEXT,
	birth_date DATETIME,
	birth_place DATETIME,
	permanent_residence TEXT,
	country TEXT,
	id_number TEXT,
	telephone_number TEXT,
	email TEXT,
	legal_representative TEXT,
	health_problems TEXT,
	food_preferences TEXT,
	scout_unit TEXT,
	tshirt TEXT,
	scarf TEXT,
	notes TEXT,
	created_at DATETIME,
	updated_at DATETIME,
	patrol_leader_id int,
	patrol_name TEXT,
	drivers_license TEXT,
	languages TEXT,
	skills TEXT,
	preferred_position TEXT,
	role TEXT,
	swimming TEXT,
	arrival_date TEXT,
	departue_date TEXT,
	uploaded_filename TEXT,
	uploaded_original_filename TEXT,
	uploaded_contenttype TEXT
);

create table payment
(
	id INTEGER
		primary key autoincrement,
	variable_symbol TEXT not null,
	price TEXT not null,
	currency TEXT not null,
	status TEXT not null,
	purpose TEXT not null,
	account_number TEXT not null,
	created_at DATETIME,
	updated_at DATETIME,
	participant_id int not null
		references participant,
	note TEXT
);

create unique index user_email_uindex
	on user (email);

insert into event (id, slug, readable_name) 
	values (1, 'test-event-slug', 'test-event-readable-name');
                                                                                                                                                                                                                                                                                                                                                                                                                                                                  
																																																																																																																 )
