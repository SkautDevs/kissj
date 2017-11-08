<?php
return [
	'settings' => [
		'debug' => true, // enable Whoops debugger
		'displayErrorDetails' => true, // set to false in production
		'addContentLengthHeader' => false, // Allow the web server to send the content-length header
		
		
		// PHPmailer settings
		'mailer' => [
			'smtp_server' => 'localhost',
			'smtp_username' => '',
			'smtp_password' => '',
			
			'from_mail' => 'registration@localhost.cz', //registration mail
			'from_name' => 'Registrace Localhost',
			
			'bcc_mail' => 'registration@localhost.cz', //registration mail for example
			'bcc_name' => 'Registrační Asistentka',
		],
	],
];