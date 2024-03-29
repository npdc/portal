# Getting started

## Server
- Apache or nginx
- php >= 7.0
  - curl
  - gd
  - xml
  - zip
- mysql >= 5.6 or mariadb >= 10.0 <sup>[1](#postgres)</sup>
- composer
- usage of https higly recommended for entire site, for instance with a certificate of letsencrypt, site doesn't do any checks for https

<a name="postgres">1</a>: As within the NPDC we use MariaDB support for PostgreSQL is not getting any attention. There are currently problems with the portal software on PostgreSQL. If needed it is most likely possible to restore full functionality using PostgreSQL, but for now this isn't high on the list of priorities for enhancement of the system.

### Apache specific config
- Allow .htaccess

### Nginx specific config
- In site configuration
```
index index.php;
root /<path to npdc code>/web;
location / {
  try_files $uri $uri/ /index.php$is_args$args;
}

location ~ \.php$ {
  include snippets/fastcgi-php.conf;
  fastcgi_pass unix:/<socket_name>.sock;
}
```
The name of the socket has to be the same as defined in the `/etc/php/<php_version>/fpm/pool.d/<pool>`

### Dev and test mode
The code contains a dev mode and a test mode. In dev mode debugging info is given at the bottom of the page, in test mode no extra info is given. These mode can be recognized by the red (dev) or orange (test) background and the word development or test in the top left corner of every page.

#### Setting in nginx
Add `fastcgi_param ENVIRONMENT dev;` for dev mode or `fastcgi_param ENVIRONMENT test;` for test mode to the `location ~ \.php$` block of the server config just above the `fastcgi_pass` line.

## Setting up datebase and config
- Clone or unpack the script to a server either in webroot or subfolder
- Create folder `data` in private folder with `chmod 777`
- Create folder `download` in private folder with `chmod 777`
- Load dependencies using composer (`cd private`, `composer install`)
- Create an empty database with username and password
- Run `private/npdc/sql/createDb.sql` on database to create all tables
- Copy `private/npdc/config.template.php` to `private/npdc/config.php` and update details
- Create first user account
  - Register through 'Create account'
  - Go into database and change `user_level` of newly created user to admin
  - After this you can use the interface to create/change other accounts

## Cron
Cron is used for synchronizing the local vocabularies with the ones of the GCMD. To prevent unauthorised cron runs you can set a cronkey in `config.php`, when a key has been set you have to run cron with `?key=<your key>`

Cron is in the main folder of the site, so run cron as `<domain>[/<path_to_portal>]/cron.php?key=<your key>` (where path_to_portal is only needed when you installed the portal in a subfolder of the webroot

We recommend to run cron daily (we suggest early morning local time) to work with the most recent vocabylaries

## Styling
- Change colors in `private/npdc/scss/p_base_colors.scss` to match your style and compile scss (in VS code with gulp-task `build:css`)
- Place logo (200x200px) at `img/logo.png`
- Place header image (at least 1240x120px) at `img/title_bg.jpg`
- If you have one you can place your favicon.ico also in the img folder
- When changing your css also increase version number in `private/npdc/version` (recommended to append with something to keep versioning in line with git repo), this can be done by running the gulp taks `bump:test`.
- In `private/npdc/template` Copy `footer.example.php` to `footer.tpl.php` and edit to your liking

## Front blocks
The blocks on the front page are filled from different sources
- The news table in the database (currently no interface to add news)
- Recently changed content on the website
- The twitter feed of the account given in config
