create table user
(
  id INTEGER
    primary key
  autoincrement,
  email TEXT not null
)
;

create table participant
(
  id INTEGER
    primary key
  autoincrement,
  first_name TEXT not null,
  last_name TEXT not null,
  birth_date DATETIME not null,
  phone TEXT not null,
  country TEXT not null,
  "group" TEXT not null
)
;

create unique index user_email_uindex
  on user (email)
;

CREATE TABLE login_token
(
  token   TEXT NOT NULL,
  user_id INT
    CONSTRAINT login_tokens_users_id_fk
    REFERENCES user (id),
  created DATETIME,
  used    BOOLEAN
);
