#!/usr/bin/env bash

./composer.phar check
vendor/bin/phpstan analyse src

# TODO add tests + phpstan
