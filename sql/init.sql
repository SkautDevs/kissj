CREATE TABLE user
(
	id                  INTEGER	PRIMARY KEY	autoincrement,
	email               TEXT NOT NULL,
  role_id             INT CONSTRAINT user_role_id_fk REFERENCES role (id)
);

CREATE TABLE patrolparticipant
(
	id                   INTEGER PRIMARY KEY autoincrement,
	user_id              INT CONSTRAINT participant_patrolleader_id_fk REFERENCES patrolleader (id),
	first_name           TEXT,
	last_name            TEXT,
	nationality          TEXT,
	gender               TEXT,
	address              TEXT,
	phone                TEXT,
	email                TEXT,
	scout_unit           TEXT,
	birth_date           DATETIME,
	birth_place          TEXT,
	allergies            TEXT,
	food_preferences     TEXT,
	card_passport_number TEXT,
	notes                TEXT
);

CREATE TABLE patrolleader
(
	id                   INTEGER PRIMARY KEY autoincrement,
	user_id              INT CONSTRAINT patrolleader_user_id_fk REFERENCES USER (id),
	finished             BOOLEAN,
	patrol_name          TEXT,
	-- same as patrolparticipant
	first_name           TEXT,
	last_name            TEXT,
	nationality          TEXT,
	gender               TEXT,
	address              TEXT,
	phone                TEXT,
	email                TEXT,
	scout_unit           TEXT,
	birth_date           DATETIME,
	birth_place          TEXT,
	allergies            TEXT,
	food_preferences     TEXT,
	card_passport_number TEXT,
	notes                TEXT

);

CREATE TABLE ist
(
	id                     INTEGER PRIMARY KEY autoincrement,
	user_id                INT CONSTRAINT ist_user_id_fk REFERENCES USER (id),
	finished               BOOLEAN,
	work_preferences       TEXT,
	skills                 TEXT,
	languages              TEXT,
	arrival_date           DATETIME,
	leaving_date           DATETIME,
	car_registration_plate TEXT,
	-- same as patrolparticipant
	first_name             TEXT,
	last_name              TEXT,
	nationality            TEXT,
	gender                 TEXT,
	address                TEXT,
	phone                  TEXT,
	email                  TEXT,
	scout_unit             TEXT,
	birth_date             DATETIME,
	birth_place            TEXT,
	allergies              TEXT,
	food_preferences       TEXT,
	card_passport_number   TEXT,
	notes                  TEXT

);

CREATE UNIQUE INDEX user_email_uindex
	ON user (email);

CREATE TABLE logintoken
(
	id      INTEGER
		PRIMARY KEY
	autoincrement,
	token   TEXT NOT NULL,
	user_id INT
	CONSTRAINT login_tokens_users_id_fk
	REFERENCES USER (id),
	created DATETIME,
	used    BOOLEAN
);

CREATE TABLE role
(
  id                     INTEGER PRIMARY KEY autoincrement,
  name                   TEXT

);
