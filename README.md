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
4. add line `127.0.0.1 kissj.local` to your `/etc/hosts` file
5. Open `http://kissj.local/v2/event/test-event-slug/` in your browser


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

 - try what function `session_start()` returns
 - if false, it probably cannot write session into filesystem
 - make path from `session_save_path()` writable


## External deals usage

Kissj can be used to collect data from external deals, momentarily from Google Forms.
From paid user you can click to "Fill up some form" and it will redirect you to Google Form with your TIE code used as user handle.
After filling up the form, the data is sent back to Kissj.
Data is sent by script, which is triggered by Google Forms on submit event.

Example of the script is below - dont forget to change `POST_URL` and `DEAL_SLUG` to correct values.

```js
//var POST_URL = "https://yess.requestcatcher.com/";
var POST_URL = "https://staging.kissj.net/v3/deal/";
var DEAL_SLUG = "sfh";
var MAX_POINTS = 2;

function onSubmit(e) {
  var form = FormApp.getActiveForm();
  var allResponses = form.getResponses();
  var latestResponse = allResponses[allResponses.length - 1];
  var response = latestResponse.getItemResponses();
  var payload = {};
  for (var i = 0; i < response.length; i++) {
    var question = response[i].getItem().getTitle();
    var answer = response[i].getResponse();
    payload[question] = answer;
  }
  payload['slug'] = DEAL_SLUG;

  var grade = e?.response.getGradableItemResponses().reduce((p, e) => p += e.getScore(), 0);

  if (grade < MAX_POINTS) {
    payload['enoughPoints'] = false;
  } else {
    payload['enoughPoints'] = true;
  }

  var options = {
    "method": "post",
    "contentType": "application/json",
    "payload": JSON.stringify(payload)
  };

  UrlFetchApp.fetch(POST_URL, options);
};


```

### How to use the script

 - Go to your chosen form and from three-dot menu click "<> Script editor"
 - Paste the script into the editor (overwrite pre-filled code)
 - In the same window, choose newly created test and click "Run" - if not working, check permissions on popup window
 - If it works (kissj should return http 4xx code), you are on the good way!
 - Next, click "Triggers" from the left menu
 - Click "Create a new trigger"
 - Make sure that on "Select event source" is Form, select on "Select event type" item "On form submit" and click "Save"
 - Try to fill up the form and check if the data is sent to kissj in "Executions" from the left menu
 - if not working, check permissions by clicking "Run" button again and allow needed permissions
