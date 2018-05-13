# KISSJ - Keep It Simple Stupid for Jamborees!

kissj is scouts **registration system for national and international Scout Jamborees** with simple idea - it has to be stupidly simple!

# Core features: 

- get information from participants as easy as possible
- administrator one-click approving with automatic payment generation
- one-click exporting health, logistic and full information for later usage
- currently supporting roles: Patrol (Patrol Leader + 9 Participants) & IST
- backend full administration for event registration team - access to participants data with edit possibility
- no use of clunky & forgettable  password int process of registration

# KISSJ is not: 

- User Event Management system
- System for food distribution, health information or safety incidents repository
- bloatware

# Installation
## Bare metal with Apache2

0. get PHP somewhere 
	- for example `https://launchpad.net/~ondrej/+archive/ubuntu/php`
1. Download project
	- `git clone [this repository]`
2. Install dependencies
	- `composer install`
3. Prepare database
	- Copy `db_init.sqlite` to `db.sqlite` 
	- Run `sql/init.sql` to `db.sqlite`
4. Create local config
	- Copy `src/settings_custom_empty.php` to `src/settings_custom.php` 
5. run `composer start` (if blocked, use sudo) and visit `localhost:80` or use your favourite webserver. And you are good to go!

## Docker
1. Get latest Docker and docker-compose
 - Latest Docker Unix command: `curl -sSL https://get.docker.com/ | sh`
 - Install script for latest docker-compose `https://gist.githubusercontent.com/deviantony/2b5078fe1675a5fedabf1de3d1f2652a/raw/4516ce1aae777616e980c4645897c4ae30362b2a/install-latest-compose.sh` 
2. run `docker-compose build && docker-compose up`
3. visit `localhost:8000`


# Devstack

We use:
- [slim framework](https://www.slimframework.com/) for routing, DI and middlewares
- [LeanMapper](http://leanmapper.com) as ORM
- SQLite3 as database
- & more in `composer.json`

# Backlog

Backlog is in project github issues

# Codestyle

- tabs! (4 spaces wide)
- directories honoring Separation of Concerns
- lambda functions in routes serves "as controllers"
- KISS please

# Possible problems & fixies + trivia

#### Database could not be read

- databasefile `db.sqlite` *and its directory* must be writable by execution programm

#### Random errors on DB

- make sure you have all PHP extensions by running `composer update`

#### STMP connection error

 - if TLS is not working correctly, try set `'SMTPAuth' => false` and/or `'disable_tls' => true`

#### Local mail service at Linux/Mac

- using https://gist.github.com/raelgc/6031274 and on Linux Mint works like a charm!
- not exactly like a charm on others:
    - https://serverfault.com/questions/184138/quick-linux-mail-server-setup-for-programming
        - good to setup the aliases, so that the mail falls into your account's mailbox
        - installing procmail fixed it for me (instead of postfix)
    - easier than setting up thinderbird is just typing `mail` in commandline
 
#### Used ACSII art generator

http://patorjk.com/software/taag/#p=display&v=0&f=Banner&t=landing