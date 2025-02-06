#!/bin/sh
set -e

chmod 777 /var/www/html/scrum-right-way/upload
chmod 777 /var/www/html/scrum-right-way/var

/root/.symfony5/bin/symfony server:start --allow-all-ip
