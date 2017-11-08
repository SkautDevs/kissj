<?php
return [
	'settings' => [
		'debug' => true, // enable Whoops debugger
		'displayErrorDetails' => true, // set to false in production
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header

		// Renderer settings
		'renderer' => [
			'template_path' => __DIR__.'/../templates/',
		],

		// Monolog settings
		'logger' => [
			'name' => 'slim-app',
			'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__.'/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],

		// PHPmailer settings
		'mailer' => [
			'smtp_server' => 'localhost',
			'smtp_username' => '',
			'smtp_password' => '',

			'from_mail' => 'registration@localhost', //registration mail
			'from_name' => 'Registrace Localhost',

			'bcc_mail' => '', //registration mail for example
			'bcc_name' => '',
		],

		'db' => [
			'path' => __DIR__ . '/../db'
		],

        'eventName' => 'cej2018',
	],
];
