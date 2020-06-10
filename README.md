# KISSJ - Keep It Simple Stupid for Jamborees!

kissj is scouts **registration system for national and international Scout Jamborees** with simple idea - it has to be stupidly simple!


# Core features: 

- get information from participants as easy as possible
- administrator one-click approving with automatic payment generation
- one-click exporting health, logistic and full information for later usage
- currently supporting roles: 
   - IST (International Service Team)
   - Patrol (Patrol Leader + number of Participants, registered by Patrol Leader)
   - guests
- backend full administration for event registration team - access to participants data with edit possibility
- no use of unsafe or forgettable passwords in process of registration - you need just an email!


# KISSJ is not: 

- User Event Management system
- System for food distribution, health information or safety incidents repository
- system for program choosing or different event talk lines chooser
- accountancy software
- bloatware


# Installation

## Developement Docker quickstart
0. Clone this project by `git clone [this repository]`
1. Get latest Docker and docker-compose
 - Latest Docker Unix command: `curl -sSL https://get.docker.com/ | sh`
 - Install script for latest docker-compose `https://gist.githubusercontent.com/deviantony/2b5078fe1675a5fedabf1de3d1f2652a/raw/4516ce1aae777616e980c4645897c4ae30362b2a/install-latest-compose.sh` 
2. run `docker-compose build && docker-compose up`
3. run `composer install` in the container // TODO install composer into image
4. visit `localhost/install.php`, fill the form and hit button which init DB and create proper .env file
5. done! Visit `localhost` for app, `localhost:8025` for mails sent from app


# Devstack

- [slim framework](https://www.slimframework.com/) - handles routing, DI and middleware
- [LeanMapper](http://leanmapper.com/) as ORM
- [SQLite3](https://www.sqlite.org/) as database
- & more in `composer.json`


# Backlog

Backlog is in project github issues


# Codestyle

- PSR-2 (with exception sameline of curly bracers after function definition name)
- directories honoring Separation of Concerns
- KISS + YAGNI


# Possible problems & fixies

#### Database could not be read

- databasefile `db.sqlite` *and its directory* must be writable by execution programm

#### Random errors on DB

- make sure you have all PHP extensions by running `composer update`

#### STMP connection error

 - if TLS is not working correctly, try set `'SMTPAuth' => false` and/or `'disable_tls' => true`

#### Local mail service at Linux/Mac

- using https://gist.github.com/raelgc/6031274 and on Linux Mint works like a charm!
- on others:
    - https://serverfault.com/questions/184138/quick-linux-mail-server-setup-for-programming
        - good to setup the aliases, so that the mail falls into your account's mailbox
        - installing procmail fixed it for me (instead of postfix)
    - easier than setting up thinderbird is just typing `mail` in commandline 
