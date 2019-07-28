CREATE TABLE user (
	id    INTEGER PRIMARY KEY    autoincrement,
	email TEXT NOT NULL
);

CREATE TABLE ist (
	id                  INTEGER PRIMARY KEY autoincrement,
	userId              INT CONSTRAINT ist_userId_fk REFERENCES USER (id),
	firstName           TEXT,
	lastName            TEXT,
	nickname            TEXT,
	permanentResidence  TEXT,
	gender              TEXT,
	email               TEXT,
	legalRepresestative TEXT,
	birthDate           DATETIME,
	scarf               TEXT,
	notes               TEXT
);

CREATE UNIQUE INDEX user_email_uindex
	ON user (email);

CREATE TABLE logintoken (
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

CREATE TABLE role (
	id      INTEGER PRIMARY KEY autoincrement,
	name    TEXT,
	eventId INT CONSTRAINT role_eventId_fk REFERENCES event (id),
	status  TEXT,
	userId  INT CONSTRAINT role_userId_fk REFERENCES user (id)
);

CREATE TABLE payment (
	id             INTEGER PRIMARY KEY autoincrement,
	event          TEXT NOT NULL,
	variableSymbol TEXT NOT NULL,
	price          TEXT NOT NULL,
	currency       TEXT NOT NULL,
	status         TEXT NOT NULL,
	purpose        TEXT NOT NULL,
	accountNumber  TEXT NOT NULL,
	generatedDate  DATETIME NOT NULL,
	roleId         INT CONSTRAINT payment_roleId_fk REFERENCES role (id)
);

CREATE TABLE event (
	id                             INTEGER PRIMARY KEY autoincrement,
	slug                           TEXT NOT NULL,
	readableName                   TEXT NOT NULL,
	accountNumber                  TEXT NOT NULL,
	prefixVariableSymbol           INT  NOT NULL,
	automaticPaymentPairing        INT  NOT NULL,
	bankId                         INT  NOT NULL,
	bankApi                        TEXT,
	allowPatrols                   INT  NOT NULL,
	maximalClosedPatrolsCount      INT  NOT NULL,
	minimalPatrolParticipantsCount INT  NOT NULL,
	maximalPatrolParticipantsCount INT  NOT NULL,
	allowIsts                      INT  NOT NULL,
	maximalClosedIstsCount         INT  NOT NULL
);