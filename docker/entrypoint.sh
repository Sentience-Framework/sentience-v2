#!/usr/bin/env bash

composer install

php sentience.php migrations:init
php sentience.php migrations:run
php sentience.php dotenv:fix

exec $@
