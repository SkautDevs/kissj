<?php
/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 2017-10-25
 * Time: 21:51
 */

use Slim\App;


require_once __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/../db_test.sqlite')) {
    unlink(__DIR__.'/../db_test.sqlite');
}

// Use the application settings
$settings = require __DIR__.'/../src/settings_test.php';

// Instantiate the application
$app = new App($settings);

// Set up dependencies
require __DIR__.'/../src/dependencies.php';

//mock mailer
$app->getContainer()['mailer'] = function(C $c) {
    return new \kissj\Mailer\MockMailer();
};

/** @var \LeanMapper\Connection $conn */
$conn = $app->getContainer()->get('db');
$conn->connect();
$conn->loadFile(__DIR__.'/../sql/init.sql');
