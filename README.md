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
 - analytics: ~https://plausible.io/kissj.net~ - TODO fix
 - sentry: https://skautdevs.sentry.io/discover/homepage/
 - monitoring and logs: ~https://monitoring.kissj.net/~ - TODO fix

## Local setup with PostgreSQL

### Prerequisites

- Installed `docker`
- Installed `docker-compose`
- Installed `make`

### Setup

1. Clone this repository: `git clone https://github.com/SkautDevs/kissj.git`
2. Setup environment (dotenv) `cp .env.example .env`
3. Run the make target, so you don't have to do everything manually: `make local-dev-postgresql`
4. Open `http://localhost:8080/v2/event/test-event-slug/` in your browser


# Devstack

- [Slim framework 4](https://www.slimframework.com/) - handles routing and middleware
- [LeanMapper](http://leanmapper.com/) as ORM
- [Phinx](https://phpunit.de/) for database migrations
- [PostgreSQL](https://www.postgresql.org/) as database
- [php-di](https://php-di.org/) for dependency injection
- [PHPUnit](https://phpunit.de/) for unit and functional tests
- [PHPStan](https://phpstan.org/) for static typechecking
- & more in `composer.json`


# Backlog

Backlog is in project GitHub issues


# Standards

- PSR-3 for logging
- PSR-4 for autoloading
- PSR-7 for HTTP requests/responses
- PSR-15 for middlewares
- directories honoring Separation of Concerns
- KISS + YAGNI


# Possible problems & fixes

#### STMP connection error

 - if TLS is not working correctly (for gmail especially), try set `'SMTPAuth' => false` and/or `'disable_tls' => true`

#### Local mail service

 - use Mailhog at `http://localhost:8025/`

#### User cannot log in - after click it stays on "insert mail" page

 - try what function `session_start()` returs
 - if false, it probably cannot write session into filesystem
 - make path from `session_save_path()` writable
