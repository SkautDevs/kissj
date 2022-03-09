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

# Useful links

 - main page: https://kissj.net/
 - analytics: https://plausible.io/kissj.net
 - sentry: https://sentry.mareshq.com/organizations/skautdevs/
 - monitoring and logs: https://monitoring.kissj.net/

## Local setup with PostgreSQL

### Prerequisites

- Installed `docker`
- Installed `docker-compose`
- Installed `make`

### Setup

1. clone this repository: `git clone https://github.com/SkautDevs/kissj.git`
2. Run the make target, so you don't have to do everything manually: `make local-dev-postgresql`
3. Open `http://localhost:8080/v2/event/test-event-slug/` in your browser


# Devstack

- [Slim framework 4](https://www.slimframework.com/) - handles routing and middleware
- [LeanMapper](http://leanmapper.com/) as ORM
- [SQLite3](https://www.sqlite.org/) as database
- [php-di](https://php-di.org/) for dependency injection
- & more in `composer.json`


# Backlog

Backlog is in project github issues


# Standards

- PSR-3 for logging
- PSR-4 for autoloading
- PSR-7 for HTTP requests/responses
- PSR-15 for middlewares
- directories honoring Separation of Concerns
- KISS + YAGNI


# Possible problems & fixies

#### Database could not be read

- databasefile `db.sqlite` *and its directory* must be writable by execution program

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
