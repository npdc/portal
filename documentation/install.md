# Getting started

## Server
- Apache or nginx
- php >= 7.0
  - curl
  - gd
  - xml
  - zip
- mysql or mariadb (other systems require work on datamodel)
- composer
- usage of https higly recommended for entire site, for instance with a certificate of letsencrypt, site doesn't do any checks for https

### Apache specific config
- Allow .htaccess

### Nginx specific config
- In site configuration
```
location / {
    try_files $uri $uri/ /index.php$is_args$args;
}
```

## Setting up datebase and config
- Clone or unpack the script to a server either in webroot or subfolder
- TODO: folder permissions
- TODO: Composer for dependencies?
- Create an empty database with username and password
- Run `app/npdc/sql/createDb.sql` on database to create all tables
- Copy `app/npdc/config.template.php` to `app/npdc/config.php` and update details
- Create first user account
  - Register through 'Create account'
  - Go into database and change `user_level` of newly created user to admin
  - After this you can use the interface to create/change other accounts

## Cron
Cron is used for synchronizing the local vocabularies with the ones of the GCMD.
To prevent unauthorised cron runs you can set a cronkey in `config.php`, when a key has been set you have to run cron with `?key=<your key>`

Cron is in the main folder of the site, so run cron as `<domain>[/<path_to_portal>]/cron.php?key=<your key>` (where path_to_portal is only needed when you installed the portal in a subfolder of the webroot

We recommend to run cron daily (we suggest early morning local time) to work with the most recent vocabylaries

## Styling
- Change colors in `app/npdc/scss/p_base_colors.scss` to match your style and compile scss (in VS code with Easy Sass: `F1 > Compile all SCSS/SASS files in the project`)
- Place logo (200x200px) at `img/logo.png`
- Place header image (at least 1240x120px) at `img/title_bg.jpg`
- TODO: release.php

## Linking with Twitter
- Create account at IFTTT (if you don't have one already TODO: iets over twitter koppeling)
- Generate webhook key
  - Go to https://ifttt.com/maker_webhooks and click settings (or connect)
  - NEVER click edit connection after setup, this resets the token
  - Copy the token (URL part after `/use/`) and store in earlier created `config.php` as `$ifttt['token']`
- Create applet at https://ifttt.com/my_applets
  - Click 'New Applet'
  - Click +this:
    - Search for Webhooks
    - Click 'Receive a web request'
    - Event Name: any string you like, also store this string in `config.php` as `$ifttt['event']`, suggested value: `npdc`
    - Click Create trigger
  - Click +that:
    - Search for Twitter
    - Click 'Post a tweet' (you will be guided to connecting to Twitter if not yet connected)
    - Set Tweet Text, recommended value: `{{Value1}} {{Value2}}`
      - `{{Value1}}` is type and name of new content, eg: '[New Content] Example content'
      - `{{Value2}}` is url to content, eg: 'https://example.com/content/1'
      - So message send to twitter will be '[New Content] Example content https://example.com/content/1' in this example
    - Click Create action
  - Click Finish