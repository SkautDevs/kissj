#!/usr/bin/env bash

composer install

composer phinx:migrate
