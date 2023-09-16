<?php

use kissj\Application\ApplicationGetter;

require __DIR__.'/../vendor/autoload.php';

session_start();
(new ApplicationGetter())->getApp()->run();
