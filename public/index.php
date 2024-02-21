<?php

use kissj\Application\ApplicationGetter;

require __DIR__.'/../vendor/autoload.php';

(new ApplicationGetter())->getApp()->run();
