<?php

// TODO fix problem with "public" subdirectory in URI (not nice)

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->group("/".$settings['settings']['eventName'], function () {
	
	// REGISTRATION & LOGIN
	
	$this->get("/registration", function (Request $request, Response $response, array $args) {
		return $this->view->render($response, 'registration.twig', ['router' => $this->router]);
	})->setName('registration');
	
	$this->post("/signup", function (Request $request, Response $response, array $args) {
		$email = $request->getParsedBodyParam("email");
		$user = $this->userService->registerUser($email);
		$this->userService->sendLoginLink($email);
		return $this->view->render($response, 'signed-up.twig', ['email' => $email]);
	})->setName('signup');
	
	
	$this->get("/login/{token}", function (Request $request, Response $response, array $args) {
		$loginToken = $args['token'];
		if ($this->userService->isLoginValid($loginToken)) {
			$role = $this->userService->getUser($loginToken)->getRole();
			switch ($role) {
				case 'patrol-leader': {
					return $response->withRedirect($this->get('router')->pathFor('pl-dashboard'));
				}
				case 'ist': {
					return $response->withRedirect($this->get('router')->pathFor('ist-dashboard'));
				}
				case 'guest': {
					return $response->withRedirect($this->get('router')->pathFor('guest-dashboard'));
				}
				default: {
					throw new Exception('Unknown role');
				}
			}
		} else {
			// TODO - add bad login screen
			return $response->withRedirect("TODO - add bad login screen");
		}
	});
	
	// PATROLS
	// TODO discuss - should be this joined?
	
	// PATROL-LEADER
	
	$this->group("/patrol-leader", function () {
		
		$this->get("/dashboard", function (Request $request, Response $response, array $args) {
			return $this->renderer->render($response, 'pl-dashboard.html', $args);
		})->setName('pl-dashboard');
		
		$this->get("/addParticipant", function (Request $request, Response $response, array $args) {
			return $this->renderer->render($response, 'addParticipant-patrol.html', $args);
		});
		
		$this->post("/addParticipant", function (Request $request, Response $response, array $args) {
			// TODO process
			return $response->withRedirect($this->get('router')->pathFor('pl-dashboard'));
		});
	});
	
	// PARTICIPANT
	
	$this->group("/participant", function () {
		
		$this->get("/details[/{id}]", function (Request $request, Response $response, array $args) {
			return $this->renderer->render($response, 'participant-details.html', $args);
		});
		
		$this->post("/details[/{id}]", function (Request $request, Response $response, array $args) {
			// TODO process
			return $response->withRedirect("TODO");
		});
		
		$this->post("/delete/{id}", function (Request $request, Response $response, array $args) {
			// TODO process
			return $response->withRedirect("TODO");
		});
		
	});
	
	// IST
	
	$this->group("/ist", function () {
		
		$this->get("/dashboard", function (Request $request, Response $response, array $args) {
			return $this->renderer->render($response, 'view-ist.html', $args);
		})->setName('ist-dashboard');
		
		$this->get("/details[/{id}]", function (Request $request, Response $response, array $args) {
			return $this->renderer->render($response, 'participant-details.html', $args);
		});
		
		$this->post("/details[/{id}]", function (Request $request, Response $response, array $args) {
			// TODO process
			return $response->withRedirect("TODO");
		});
	});
	
	// GUESTS
	
	// TODO
	
	// LANDING PAGE
	
	$this->get("", function (Request $request, Response $response, array $args) {
		return $this->view->render($response, 'landing-page.twig', ['router' => $this->router]);
	})->setName("landing");
});
