<?php

require __DIR__.'/../vendor/autoload.php';

session_start();

$app = (new \kissj\Application\ApplicationGetter())->getApp();

// Run app
$app->run();
