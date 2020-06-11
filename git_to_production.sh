#!/bin/bash

# first perform git pull (because development is also done on other machines
git pull

# refresh css and js
gulp build:css
gulp build:js

#send files to server
scp -r /home/mtacoma/npdc/portal/private/npdc npdc:/private/
scp -r /home/mtacoma/npdc/portal/web/* npdc:/web/
