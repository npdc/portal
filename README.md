Dependencies:
- Apache or nginx
- php 7.0
  - curl
  - gd
  - xml
  - zip
- postgresql (other dbms could work as well, but might require work on the models)
  - postgis

For generating compressed js/css
- Yui compiler
with /usr/bin/yc:
#!/bin/sh
java -jar /usr/share/yui-compressor/yui-compressor.jar "$@"
- sass
