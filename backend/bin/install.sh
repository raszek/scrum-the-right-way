#!/bin/bash

set -e

./bin/console doctrine:database:drop --force
./bin/console doctrine:database:create
./bin/console doctrine:migrations:migrate --no-interaction
./bin/console cache:clear
./bin/console doctrine:fixtures:load --group=install --no-interaction

./bin/console app:create-admin
