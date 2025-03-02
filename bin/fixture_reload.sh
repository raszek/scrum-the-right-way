#!/bin/bash

set -e

./bin/console doctrine:fixtures:load --no-interaction --no-debug
