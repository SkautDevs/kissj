<?php

use \Psr\Container\ContainerInterface as C;
use Src\ParticipantService;
use Src\UserService;

// DIC configuration

$container = $app->getContainer();

// view renderer
$container['renderer'] = function (C $c) {
	$settings = $c->get('settings')['renderer'];
	return new Slim\Views\PhpRenderer($settings['template_path']);
	
};

// monolog
$container['logger'] = function (C $c) {
	$settings = $c->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	
	return $logger;
};

// PHPmailes
$container['mailer'] = function (C $c) {
	$settings = $c->get('settings')['mailer'];
	$mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
	
	//Server settings
	$mailer->SMTPDebug = 2;	// Enable verbose debug output
	$mailer->isSMTP();	// Set mailer to use SMTP
	$mailer->Host = $settings['smtp_server'];	// Specify main and backup SMTP servers
	$mailer->SMTPAuth = true;	// Enable SMTP authentication
	$mailer->Username = $settings['smtp_username'];	// SMTP username
	$mailer->Password = $settings['smtp_password'];	// SMTP password
	$mailer->SMTPSecure = 'tls';	// Enable TLS encryption, `ssl` also accepted
	$mailer->Port = 587;	// TCP port to connect to
	
	//Recipients
	$mailer->setFrom($settings['from_mail'], $settings['from_name']);
	$mailer->addCC($settings['bcc_mail'], $settings['bcc_name']);
	
	// Content
	$mailer->isHTML(true);
	return $mailer;
	
	/* usage:
	$mail->Subject = 'Here is the subject';
	$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
	$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
	$mail->send();
	*/
};

// db
$container['db'] = function (C $c) {
	$path = $c->get('settings')['db']['path'];
	$dsn = 'sqlite:' . $path;
	$pdo = new PDO($dsn);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	return $pdo;
};

// user service
$container['userService'] = function (C $c) {
	$service = new UserService($c->get('db'));
	return $service;
};

// participant service
$container['participantService'] = function (C $c) {
	$service = new ParticipantService($c->get('db'));
	return $service;
};
