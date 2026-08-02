# kissj - Keep It Simple Stupid for Jamborees!

kissj is scouts **registration system for national and international Scout Jamborees** with simple idea - it has to be stupidly simple!

### Core features: 

- get information from participants as easy as possible
- administrator one-click approving with automatic payment generation
- one-click exporting health, logistic and full information for later usage
- currently supporting roles: 
   - IST (International Service Team)
   - Patrol - Patrol Leader + number of Participants, registered all by Patrol Leader
   - Troops - Troop Leader + number of Troop Participants, all registering separately and joingin into one Troop after
   - guests
- full administration for event registration team - access to participants data with edit possibility
- no use of unsafe or forgettable passwords in process of registration - you need just an email or Skautis account!


### KISSJ is not: 

- User Event Management system
- System for food distribution, health information or safety incidents repository
- accountancy software
- bloatware

### Useful links

 - main page: https://kissj.net/
 - sentry: https://skautdevs.sentry.io/discover/homepage/


# 🏕️ Never programmed before? Start here

You do **not** need to know PHP, SQL or any framework to get kissj running on your computer.
You need to copy-paste a few commands. Takes about half an hour, most of it waiting for downloads.

## Step 1 - install the tools

You need three things: **Docker** (runs the whole app for you), **Git** (downloads the code) and **Make** (runs the commands for you).

<details>
<summary><b>Windows</b></summary>

1. Download and install **Docker Desktop**: https://www.docker.com/products/docker-desktop/
   Keep the option *"Use WSL 2 instead of Hyper-V"* checked. Restart the computer when asked.
2. Open the **Start menu → type "Ubuntu"** and open it. A black terminal window appears - you will type everything there.
   *No Ubuntu in the Start menu?* Open **PowerShell as administrator**, run `wsl --install`, restart, then try again.
3. In Docker Desktop go to **Settings → Resources → WSL Integration** and switch **Ubuntu** on.
4. In the Ubuntu terminal, install Git and Make:
   ```bash
   sudo apt update && sudo apt install -y git make
   ```
</details>

<details>
<summary><b>macOS</b></summary>

1. Download and install **Docker Desktop**: https://www.docker.com/products/docker-desktop/
   (pick the *Apple Silicon* version on M1/M2/M3/M4 Macs, *Intel* on older ones)
2. Open the **Terminal** app and install Git and Make:
   ```bash
   xcode-select --install
   ```
</details>

<details>
<summary><b>Linux (Ubuntu / Debian)</b></summary>

```bash
sudo apt update && sudo apt install -y docker.io docker-compose git make
sudo usermod -aG docker $USER
```
Then log out and log back in, so the last command takes effect.
</details>

## Step 2 - download kissj and start it

Copy-paste these four lines into the terminal, one by one:

```bash
git clone https://github.com/SkautDevs/kissj.git
cd kissj
cp deploy/dev/compose.env.example deploy/dev/compose.env
make dev-up
```

The last one takes a few minutes the first time - it is downloading the database, PHP and everything else.
Nothing to fill in, nothing to configure - the copied settings file already works.

> **Windows tip:** run this in your Ubuntu home folder (just `cd ~` first), **not** in `/mnt/c/...` - it is many times faster there.

## Step 3 - open it

👉 **http://localhost:8080/v2/event/test-event-slug/**

That is your own copy of kissj, running on your computer. Nothing you do here touches the real one.

Two things to know:

- **Logging in needs no password** - you type an email and kissj sends you a link.
  The mail never leaves your computer, you pick it up at 👉 **http://localhost:8025/** (a fake mailbox called MailHog).
- **`make dev-down`** stops everything, **`make dev-up`** starts it again. Your data stays.

## Step 4 - your first change

Start with **text**, not with code. Open the file `src/Templates/en.yaml` in any editor, change some sentence, save,
refresh the browser - and there it is. That is the whole loop.

The same texts exist in three languages, so a text change belongs in all three files:
`src/Templates/cs.yaml` (Czech), `src/Templates/sk.yaml` (Slovak), `src/Templates/en.yaml` (English).

When that feels boring, move on in this order:

1. **Texts** - `src/Templates/*.yaml` (no programming at all)
2. **Pages** - `src/Templates/translatable/*.twig` - the HTML of the pages, with `{{ placeholders }}` for the texts
3. **Code** - the PHP in `src/` - ask someone to point you at a small first task

Before you show your change to anyone, let the computer check it for you:

```bash
docker exec -u 1000 -it kissj-app-php-fpm-1 composer test
```

## If something goes wrong

- **Page does not load** - is Docker Desktop running? Then `make dev-down` and `make dev-up` again.
- **`make: command not found`** - step 1 was not finished.
- **Anything else** - open an issue on GitHub, or ask in the Scout dev channel. Asking early is normal here.

# Local development

### Prerequisites

- Installed `docker`
- Installed `docker-compose`
- Installed `make`

### Setup

1. Clone this repository: `git clone https://github.com/SkautDevs/kissj.git`
2. Copy `deploy/dev/compose.env.example` to `deploy/dev/compose.env` and fill in the values
3. Run the make target dev-up, so you don't have to do it manually: `make dev-up`
4. add line `127.0.0.1 kissj.local` to your `/etc/hosts` file(optional)
5. Open `http://kissj.local/v2/event/test-event-slug/` in your browser and you are good to go!
6. To stop the containers and remove them, run `make dev-down`(data should be preserved)

### Devstack

- [Slim framework 4](https://www.slimframework.com/) - handles routing and middleware
- [LeanMapper](http://leanmapper.com/) as ORM
- [Phinx](https://phpunit.de/) for database migrations
- [PostgreSQL](https://www.postgresql.org/) as database
- [php-di](https://php-di.org/) for dependency injection
- [PHPUnit](https://phpunit.de/) for unit and functional tests
- [PHPStan](https://phpstan.org/) for static typechecking
- & more in `composer.json`

### Backlog & roadmap

Backlog is in project GitHub issues, roadmap is in project GitHub milestones


# Possible problems & fixes

### STMP connection error

 - if TLS is not working correctly (for gmail especially), try set `'SMTPAuth' => false` and/or `'disable_tls' => true`

### Local mail service

 - use Mailhog at `http://localhost:8025/`

### Local Skautis login service

 - put AppID into database in the `event` table
 - use command like `socat TCP-LISTEN:80,fork,reuseaddr TCP:127.0.0.1:8080` to reroute from :80 into :8080 locally if needed

### Reroute ports for Skautis

 - if you want to use local Skautis login service, you need to reroute port 80 into 8080
 - Skautis after login is returning to local :80 port on `http://localhost/` and you need `http://localhost:8080/`
 - command: `sudo socat TCP-LISTEN:80,fork,reuseaddr TCP:127.0.0.1:8080`

# HOWTOs

### How to change docker image

 - for PHP edit file `deploy/container_images/php/Containerfile-ubi`
 - run build `docker build . -f deploy/container_images/php/Containerfile-ubi -t quay.io/kissj/php-ubi`
 - pull new images from server `docker-compose pull`

### External deals usage

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
var AUTH_KEY = 'kissj-event-auth-key';

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
    "headers": {
      "Authorization": "Bearer " + AUTH_KEY,
    },
    "contentType": "application/json",
    "payload": JSON.stringify(payload)
  };

  UrlFetchApp.fetch(POST_URL, options);
};
```

#### How to use the script

 - Go to your chosen form and from three-dot menu click "<> Script editor"
 - Paste the script into the editor (overwrite pre-filled code)
 - In the same window, choose newly created test and click "Run" - if not working, check permissions on popup window
 - If it works (kissj should return http 4xx code), you are on the good way!
 - Next, click "Triggers" from the left menu
 - Click "Create a new trigger"
 - Make sure that on "Select event source" is Form, select on "Select event type" item "On form submit" and click "Save"
 - Try to fill up the form and check if the data is sent to kissj in "Executions" from the left menu
 - if not working, check permissions by clicking "Run" button again and allow needed permissions
