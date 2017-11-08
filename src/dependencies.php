<?php

use kissj\Mapper;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use \Psr\Container\ContainerInterface as C;

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
	$connection = new LeanMapper\Connection([
		'driver'   => 'sqlite3',
		'database' => $path,
	]);

	return $connection;
};

// db_mapper
$container['dbMapper'] = function (C $c) {
	$mapper = new Mapper();
	return $mapper;
};

// db_mapper
$container['dbFactory'] = function (C $c) {
	$entityFactory = new LeanMapper\DefaultEntityFactory;
	return $entityFactory;
};

$container['userRepository'] = function (C $c) {
	$service = new UserRepository($c->get('db'), $c->get('dbMapper'), $c->get('dbFactory'));
	return $service;
};

$container['tokenRepository'] = function (C $c) {
	$service = new LoginTokenRepository($c->get('db'), $c->get('dbMapper'), $c->get('dbFactory'));
	return $service;
};

// user service
$container['userService'] = function (C $c) {
	$service = new UserService($c->get('userRepository'), $c->get('tokenRepository'), $c->get('mailer'));
	return $service;
};

// participant service
$container['participantService'] = function (C $c) {
	$service = new ParticipantService($c->get('db'));
	return $service;
};

$container['view'] = function ($c) {
	$view = new \Slim\Views\Twig(dirname(__FILE__) . '/../templates/', [
		'cache' => false ? dirname(__FILE__) . '/../temp/twig' : false
	]);

	// Instantiate and add Slim specific extension
	$basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
	$view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));

	return $view;
};

