<?php
$settings = [
	'settings' => [
		'debug' => false, // keep disable Whoops debugger
		'displayErrorDetails' => false, // set to false in production
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header
		
		// Renderer settings
		'renderer' => [
			'template_path' => __DIR__.'/../templates/',
		],
		
		// Monolog settings
		'logger' => [
			'name' => 'slim-app',
			'path' => __DIR__.'/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],
		
		// PHPmailer settings
		'mailer' => [
			'smtp_server' => 'localhost',
			'smtp_port' => 587,
			'smtp_username' => '',
			'smtp_password' => '',
			
			'from_mail' => 'registration@localhost.cz', //registration mail
			'from_name' => 'Registrace Localhost',
			
			'bcc_mail' => 'registration@localhost.cz', //registration mail for example
			'bcc_name' => 'Registrační Asistentka',
		],
		
		'db' => [
			'path' => __DIR__.'/../db.sqlite'
		],
		
		'eventName' => 'cej2018',
	],
];

$settings = require('settings_custom.php') $settings;

return $settings;