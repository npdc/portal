**Features:**
- Project descriptions
- Dataset descriptions according to DIF10 format (GCMD standard)
- Publication descriptions
- Responsive
![Screenshot](documentation/screenshot.png)

**Dependencies:**
- Apache or nginx
- php 7.0
  - curl
  - gd
  - xml
  - zip
- mysql of mariadb (other systems require work on datamodel)
- composer

**For generating compressed js/css**
- visual studio code
  - Easy Sass
  - minify

**To be documented**
- IFTTT hook for twitter
- how to run cron
- creating first user account
- instruction on creating own css

Getting started
---------------
**Server**
- Apache or nginx
- php 7.0
  - curl
  - gd
  - xml
  - zip
- mysql of mariadb (other systems require work on datamodel)
- composer
- usage of https higly recommended for whole site, for instance with a certificate of letsencrypt, site doesn't do any checks for https

**Setting up datebase and config**
- Unpack the script to a server
- Create a database with user and password
- Run app/npdc/sql/createDb.sql on database to create all tables
- Create app/npdc/config.php from app/npdc/config.template.php and update details
- Run cron to populate vocabs (recommended to run cron daily to keep vocabs updated)
- Create first user account
  - Register trough 'Create account'
  - Go into database and change user_level of newly created user to admin
  - After this you can use the interface to create/change other persons

**Styling**
- Change colors in app/npdc/scss/p_base_colors.scss to match your style and compile scss (in VS code with Easy Sass: f1 > Compile all SCSS/SASS files in the project)
- Place logo (200x200px) at img/logo.png
- Place header image (at least 1240x120px) at img/title_bg.jpg
- TODO: release.php

**Linking with Twitter**
- Create account at IFTTT
- Generate webhook key
  - Go to https://ifttt.com/maker_webhooks and click settings (or connect)
  - Copy the key (after /use/) and store in config.php
  - NEVER click edit connection after setup, this resets the key
- Create applet
  - This: Webhook, eventname: what you put in config.php as event
  - That: Post a tweet (you will be guided to connecting to Twitter)
    - Tweet text: value1 value2 (or anything else you prefer)