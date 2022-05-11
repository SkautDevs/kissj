#!/usr/bin/env bash

composer install --no-interaction

composer phinx:migrate --no-interaction
