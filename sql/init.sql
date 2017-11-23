CREATE TABLE user
(
	id      INTEGER PRIMARY KEY    autoincrement,
	email   TEXT NOT NULL,
	roleId INT CONSTRAINT user_roleId_fk REFERENCES role (id)
);

CREATE TABLE patrolparticipant
(
	id                 INTEGER PRIMARY KEY autoincrement,
	userId             INT CONSTRAINT participant_patrolleaderId_fk REFERENCES patrolleader (id),
	firstName          TEXT,
	lastName           TEXT,
	nationality        TEXT,
	gender             TEXT,
	address            TEXT,
	phone              TEXT,
	email              TEXT,
	scoutUnit          TEXT,
	birthDate          DATETIME,
	birthPlace         TEXT,
	allergies          TEXT,
	foodPreferences    TEXT,
	cardPassportNumber TEXT,
	notes              TEXT
);

CREATE TABLE patrolleader
(
	id                 INTEGER PRIMARY KEY autoincrement,
	userId             INT CONSTRAINT patrolleader_userId_fk REFERENCES USER (id),
	finished           BOOLEAN,
	patrolName         TEXT,
	-- same as patrolparticipant
	firstName          TEXT,
	lastName           TEXT,
	permanentResidence TEXT,
	telephoneNumber    TEXT,
	nationality        TEXT,
	gender             TEXT,
	address            TEXT,
	country            TEXT,
	phone              TEXT,
	email              TEXT,
	scoutUnit          TEXT,
	birthDate          DATETIME,
	birthPlace         TEXT,
	allergies          TEXT,
	foodPreferences    TEXT,
	cardPassportNumber TEXT,
	notes              TEXT

);

CREATE TABLE ist
(
	id                   INTEGER PRIMARY KEY autoincrement,
	userId               INT CONSTRAINT ist_userId_fk REFERENCES USER (id),
	finished             BOOLEAN,
	workPreferences      TEXT,
	skills               TEXT,
	languages            TEXT,
	arrivalDate          DATETIME,
	leavingDate          DATETIME,
	carRegistrationPlate TEXT,
	-- same as patrolparticipant
	firstName            TEXT,
	lastName             TEXT,
	nationality          TEXT,
	gender               TEXT,
	address              TEXT,
	phone                TEXT,
	email                TEXT,
	scoutUnit            TEXT,
	birthDate            DATETIME,
	birthPlace           TEXT,
	allergies            TEXT,
	foodPreferences      TEXT,
	cardPassportNumber   TEXT,
	notes                TEXT

);

CREATE UNIQUE INDEX user_email_uindex
	ON user (email);

CREATE TABLE logintoken
(
	id      INTEGER
		PRIMARY KEY
	autoincrement,
	token   TEXT NOT NULL,
	userId  INT
	CONSTRAINT login_tokens_users_id_fk
	REFERENCES USER (id),
	created DATETIME,
	used    BOOLEAN
);

CREATE TABLE role
(
	id   INTEGER PRIMARY KEY autoincrement,
	name TEXT

);
