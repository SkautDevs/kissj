<?php

use kissj\Orm\Mapper;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Patrol\PatrolService;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\Ist\IstService;
use kissj\User\LoginTokenRepository;
use kissj\User\UserRepository;
use kissj\User\UserService;
use \kissj\User\RoleRepository;
use \Psr\Container\ContainerInterface as C;

$container = $app->getContainer();

$container['logger'] = function (C $c) {
	$settings = $c->get('settings')['logger'];
	$logger = new Monolog\Logger($settings['name']);
	$logger->pushProcessor(new Monolog\Processor\UidProcessor());
	$logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
	
	return $logger;
};

$container['mailer'] = function (C $c) {
	$settings = $c->get('settings');
	return new \kissj\Mailer\PhpMailerWrapper($settings['mailer']);
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

$container['dbFactory'] = function (C $c) {
	return new LeanMapper\DefaultEntityFactory;
};

// repositories
$container['userRepository'] = function (C $c) {
	return new UserRepository(
		$c->get('db'),
		$c->get('dbMapper'),
		$c->get('dbFactory'));
};

$container['tokenRepository'] = function (C $c) {
	return new LoginTokenRepository(
		$c->get('db'),
		$c->get('dbMapper'),
		$c->get('dbFactory'));
};

$container['patrolParticipantRepository'] = function (C $c) {
	return new PatrolParticipantRepository(
		$c->get('db'),
		$c->get('dbMapper'),
		$c->get('dbFactory'));
};

$container['patrolLeaderRepository'] = function (C $c) {
	return new PatrolLeaderRepository(
		$c->get('db'),
		$c->get('dbMapper'),
		$c->get('dbFactory'));
};

$container['istRepository'] = function (C $c) {
	return new IstRepository(
		$c->get('db'),
		$c->get('dbMapper'),
		$c->get('dbFactory'));
};

$container['roleRepository'] = function (C $c) {
	return new RoleRepository(
		$c->get('db'),
		$c->get('dbMapper'),
		$c->get('dbFactory'));
};

// services
$container['userService'] = function (C $c) {
	return new UserService(
		$c->get('userRepository'),
		$c->get('roleRepository'),
		$c->get('tokenRepository'),
		$c->get('mailer'),
		$c->get('router'),
		$c->get('random'),
		$c->get('settings')['eventName'],
		$c->get('view'),
		$c->get('settings')['possibleUserRoles']);
};

$container['userStatusService'] = function (C $c) {
	return new \kissj\User\UserStatusService();
};

$container['patrolService'] = function (C $c) {
	$eventSettings = $c->get('settings')['event'];
	return new PatrolService(
		$c->get('patrolParticipantRepository'),
		$c->get('patrolLeaderRepository'),
		$c->get('roleRepository'),
		$c->get('userStatusService'),
		$c->get('flashMessages'),
		$eventSettings);
};

$container['istService'] = function (C $c) {
	$eventSettings = $c->get('settings')['event'];
	return new IstService(
		$c->get('istRepository'),
		$c->get('roleRepository'),
		$c->get('userStatusService'),
		$c->get('flashMessages'),
		$eventSettings);
};

// views
$container['flashMessages'] = function (C $c) {
	return new kissj\FlashMessages\FlashMessagesBySession();
};

$container['view'] = function (C $c) {
	$rendererSettings = $c->get('settings')['renderer'];
	
	$view = new \Slim\Views\Twig($rendererSettings['templates_path'], [
		'cache' => $rendererSettings['enable_cache'] ? dirname(__FILE__).'/../temp/twig' : false
	]);
	
	// Instantiate and add Slim specific extension
	$uri = $c['request']->getUri();
	$basePath = rtrim(str_ireplace('index.php', '', $uri->getScheme().'://'.$uri->getHost().$uri->getBasePath()), '/');
	$baseHostScheme = $uri->getScheme().'://'.$uri->getHost();
	$view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));
	$view->getEnvironment()->addGlobal('baseHostScheme', $baseHostScheme);
	$view->getEnvironment()->addGlobal('flashMessages', $c['flashMessages']);
	
	return $view;
};