#!/usr/bin/env bash

composer install

php sentience.php migrations:init
php sentience.php migrations:apply
php sentience.php dotenv:fix

exec $@
