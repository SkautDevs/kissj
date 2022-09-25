#!/usr/bin/env bash

if [ -f /usr/bin/dnf ]; then dnf install -y php-pecl-xdebug3; fi

composer install --no-interaction

composer phinx:migrate --no-interaction
