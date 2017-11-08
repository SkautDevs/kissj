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


## Local mail service

 - using https://gist.github.com/raelgc/6031274 and it works like a charm!
 
# Devstack
We use:
- [slim framework](https://www.slimframework.com/) for routing and DI
- [LeanMapper](http://leanmapper.com) as ORM