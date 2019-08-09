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
            'templates_path' => __DIR__.'/Templates/',
			'enable_cache' => true,
		],

		// Monolog settings
		'logger' => [
			'name' => 'slim-app',
			'path' => __DIR__.'/../logs/app.log',
			'level' => \Monolog\Logger::DEBUG,
		],

		// PHPmailer settings
		'mailer' => [
			'smtp' => true,
			'smtp_server' => 'localhost',
			'smtp_auth' => true,    // SMTP authentication
			'smtp_port' => 587,
			'smtp_username' => '',
			'smtp_password' => '',
			'smtp_secure' => 'ssl', // ssl for Gmail, tls also possible

			'from_mail' => 'registration@localhost.cz', // registration mail
			'from_name' => 'Registrace Localhost',

			'bcc_mail' => '', // another mail
			'bcc_name' => '',

			// debugging settings
			'disable_tls' => false, // turn off all certificate check
			'debugOutoutLevel' => 0, // print debug level (0 min to 4 max)
			'sendMailToMainRecipient' => true, // set false in localhost
		],

		'db' => [
			'path' => __DIR__.'/../db.sqlite'
		],

		'eventName' => 'cej2018',

		'adminer' => [
			// change password & add this into your settings_custom please
			// 'login' => 'superSecretUsername',
			// 'password' => 'superSecretPassword',
		],

		'event' => [
			'minimalPatrolParticipantsCount' => 9,
			'maximalPatrolParticipantsCount' => 9,

			'maximalClosedPatrolsCount' => 25,
			'maximalClosedIstsCount' => 100,
		],

		// TODO rename to 'payment'
		'paymentSettings' => [
			'maxElapsedPaymentDays' => 14,
			'prefixVariableSymbol' => '00',
			'fioApiToken' => 'fio API token',
			'accountNumber' => '0123456789',
			'scarfPrice' => 70,
		],
	],
];

if (file_exists(__DIR__.'/settings_custom.php')) {
	$settings = array_replace_recursive($settings, require 'settings_custom.php');
}

return $settings;
