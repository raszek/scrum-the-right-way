#!/bin/bash

set -e

./bin/console doctrine:database:drop --force
./bin/console doctrine:database:create
./bin/console doctrine:migrations:migrate --no-interaction
./bin/console cache:clear
./bin/console doctrine:fixtures:load --no-interaction --no-debug


./bin/console doctrine:database:drop --force --env=test
./bin/console doctrine:database:create --env=test
./bin/console doctrine:migrations:migrate --no-interaction --env=test
