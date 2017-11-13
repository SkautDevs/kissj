<?php

use kissj\Mapper;
use kissj\Patrol\ParticipantRepository;
use kissj\Patrol\PatrolService;
use kissj\Patrol\PatrolLeaderRepository;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use \Psr\Container\ContainerInterface as C;

$container = $app->getContainer();

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
	return new \kissj\Mailer\PhpMailerWrapper($c->get('settings')['mailer']);
};

$container['random'] = function (C $c) {
	return new \kissj\Random();
};

// db
$container['db'] = function (C $c) {
	$path = $c->get('settings')['db']['path'];
	$connection = new LeanMapper\Connection([
		'driver' => 'sqlite3',
		'database' => $path,
	]);
	
	return $connection;
};

// db_mapper
$container['dbMapper'] = function (C $c) {
	return new Mapper();
};

// db_mapper
$container['dbFactory'] = function (C $c) {
	return new LeanMapper\DefaultEntityFactory;
};

$container['userRepository'] = function (C $c) {
	return new UserRepository($c->get('db'), $c->get('dbMapper'), $c->get('dbFactory'));
};

$container['tokenRepository'] = function (C $c) {
	return new LoginTokenRepository($c->get('db'), $c->get('dbMapper'), $c->get('dbFactory'));
};

$container['participantRepository'] = function (C $c) {
	return new ParticipantRepository($c->get('db'), $c->get('dbMapper'), $c->get('dbFactory'));
};

$container['patrolLeaderRepository'] = function (C $c) {
	return new PatrolLeaderRepository($c->get('db'), $c->get('dbMapper'), $c->get('dbFactory'));
};

// user service
$container['userService'] = function (C $c) {
	return new UserService($c->get('userRepository'), $c->get('tokenRepository'), $c->get('mailer'), $c->get('router'), $c->get('random'), $c->get('settings')['eventName'], $c->get('view'));
};

// participant service
$container['participantService'] = function (C $c) {
	return new PatrolService($c->get('participantRepository'), $c->get('patrolLeaderRepository'));
};

$container['flashMessages'] = function (C $c) {
	return new kissj\FlashMessages\FlashMessagesBySession();
};

$container['view'] = function ($c) {
	$view = new \Slim\Views\Twig(dirname(__FILE__).'/../templates/', [
		'cache' => false ? dirname(__FILE__).'/../temp/twig' : false
	]);
	
	// Instantiate and add Slim specific extension
	$uri = $c['request']->getUri();
	$basePath = rtrim(str_ireplace('index.php', '', $uri->getScheme() . '://'.$uri->getHost().$uri->getBasePath()), '/');
	$baseHostScheme = $uri->getScheme() . '://'.$uri->getHost();
	$view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));
	$view->getEnvironment()->addGlobal('flashMessages', $c['flashMessages']->dumpMessagesIntoArray());
	$view->getEnvironment()->addGlobal('baseHostScheme', $baseHostScheme);
	
	return $view;
};