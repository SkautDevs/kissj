<?php

use kissj\Mapper;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use \Psr\Container\ContainerInterface as C;
use Src\Participant\ParticipantService;

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
	$mailer = new \kissj\Mailer\PhpMailerWrapper($c->get('settings')['mailer']);
	
	return $mailer;
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

$container['participantRepository'] = function (C $c) {
	$service = new ParticipantRepository($c->get('db'), $c->get('dbMapper'), $c->get('dbFactory'));
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

