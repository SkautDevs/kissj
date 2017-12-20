# KISSJ - Keep It Simple Stupid for Jamborees!

kissj is scouts **registration system for national and international Scout Jamborees** with simple idea - it has to be stupidly simple!

# Core features: 

- get information from pariticipants as easy as possible
- automatic generation informations for payment 
- currently supporting roles: Patrol (Patrol Leader + 9 Participants) & IST
- backend full administration for event registration team - access to participants data with edit possibility
- no use of clunky password int process of registration

# KISSJ is not: 

- User Event Management system
- System for food distribution, health information or safety incidents repository
- bloatware

# Installation (yet)

1. Download project
`git clone [this repository]`
2. Install dependencies
`composer install`
3. Prepare database
	- Copy `db_init.sqlite` to `db.sqlite` 
	- Run `sql/init.sql` to `db.sqlite`
4. Create local config
	Copy `src/settings_custom_empty.php` to `src/settings_custom.php` 

And you are good to go!

# Devstack

We use:
- [slim framework](https://www.slimframework.com/) for routing, DI and middlewares
- [LeanMapper](http://leanmapper.com) as ORM
- SQLite3 as database
- & more in `composer.json`

# Backlog

Backlog is localized in wiki page named `Backlog`. 

# Codestyle

- tabs! (4 spaces wide)
- directories honoring Separation of Concerns
- lambda functions in routes serves "as controllers"
- KISS please

# Possible problems & fixies + trivia

#### Database could not be read
- databasefile `db.sqlite` *and its directory* must be writable by execution programm

#### STMP connection error
 - if TLS is not working corrently, try set `'SMTPAuth' => false` and/or `'disable_tls' => true`

#### Local mail service at Linux/Mac

- using https://gist.github.com/raelgc/6031274 and on Linux Mint works like a charm!
- not exactly like a charm on others:
    - https://serverfault.com/questions/184138/quick-linux-mail-server-setup-for-programming
        - good to setup the aliases, so that the mail falls into your account's mailbox
        - installing procmail fixed it for me (instead of postfix)
    - easier than setting up thinderbird is just typing `mail` in commandline
 