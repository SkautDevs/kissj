<?php

$settings = [
	'settings' => [
		'debug' => true, // true fires Whoops debugger, false falls into Slim debugger (keep true at all times)

		// Whoops debug part
		'whoopsDebug' => false, // true enable Whoops nice debug page, false fires up production error handle

		// Slim debug part
		'displayErrorDetails' => false, // set to false in production
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header

		// Testing site
		'useTestingSite' => false,

		// Renderer settings
		'renderer' => [
            'templates_path' => __DIR__.'/../Templates/',
			'enable_cache' => true,
            'cache_path' => __DIR__.'/../../temp/twig',
		],

		// Monolog settings
		'logger' => [
			'name' => 'slim-app',
			'path' => __DIR__.'/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],

        // PHPmailer settings - MailHog
		'mailer' => [
            'smtp' => true,
            'smtp_server' => 'mailhog',
            'smtp_auth' => true,    // SMTP authentication
            'smtp_port' => 1025,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_secure' => null, // ssl for Gmail, tls or nullalso possible

            'from_mail' => 'registration@localhost', // registration mail
			'from_name' => 'Registrace Localhost',

			'bcc_mail' => '', // another mail
			'bcc_name' => '',

			// debugging settings
			'disable_tls' => false, // turn off all certificate check
			'debugOutoutLevel' => 0, // print debug level (0 min to 4 max)
            'sendMailToMainRecipient' => true, // set false in dev
		],

		'db' => [
			'path' => __DIR__.'/../db.sqlite'
		],

		'adminer' => [
			// change password & add this into your settings_custom please
			// 'login' => 'superSecretUsername',
			// 'password' => 'superSecretPassword',
		],
	],
];

if (file_exists(__DIR__.'/settings_custom.php')) {
	$settings = array_replace_recursive($settings, require 'settings_custom.php');
}

return $settings;
