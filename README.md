# Slim Framework 3 Skeleton Application

kissj is scouts registration system for jamborees with simple idea - it has to be simple!

# Installation (not complete yet)

1. Download project
`git clone [this repository]`
2. Install dependencies
`composer update`
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
- ... more in `composer.json`

## Possible problems and fixies

- databasefile db.sqlite and *its directory* must be writable by execution programm

## Local mail service

- using https://gist.github.com/raelgc/6031274 and it works like a charm!
 
