<?php
/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 2017-10-25
 * Time: 21:51
 */

use Slim\App;

require_once __DIR__ . '/../vendor/autoload.php';

unlink(__DIR__ . '/../db_test');

// Use the application settings
$settings = require __DIR__ . '/../src/settings_test.php';

// Instantiate the application
$app = new App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

/** @var PDO $conn */
$conn = $app->getContainer()->get('db');
$conn->exec(file_get_contents(__DIR__ . '/../sql/init.sql'));