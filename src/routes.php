<?php

// TODO fix problem with "public" subdirectory in URI (not nice)

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->group("/".$settings['settings']['eventName'], function () {
	
	// REGISTRATION, LOGIN & LOGOUT
	
	$this->get("/registration/{role}", function (Request $request, Response $response, array $args) {
		$role = $args['role'];
		if (!$this->userService->isUserRoleValid($role)) {
			throw new Exception('User role "'.$role.'" is not valid');
		}
		// TODO translator for roles
		
		return $this->view->render($response, 'registration.twig', ['router' => $this->router, 'role' => $role]);
	})->setName('registration');
	
	
	$this->post("/signup/{role}", function (Request $request, Response $response, array $args) {
		$role = $args['role'];
		if (!$this->userService->isUserRoleValid($role)) {
			throw new Exception('User role "'.$role.'" is not valid');
		}
		$email = $request->getParsedBodyParam("email");
		
		if ($this->userService->isEmailExisting($email)) {
			$this->flashMessages->error("Nepovedlo se založit uživatele pro email $email, protože už takový existuje. Nechceš se spíš příhlásit?");
			return $response->withRedirect($this->router->pathFor('loginAskEmail'));
		}
		
		$this->userService->registerUser($email);
		try {
			$this->userService->sendLoginTokenByMail($email);
			return $response->withRedirect($this->router->pathFor('signupSuccess'));
		} catch (Exception $e) {
			$this->logger->addError("Error sending registration email to $email with token ".
				$this->userService->getTokenForEmail($email), array($e));
			$this->flashMessages->error("Registrace se povedla, ale nezdařilo se odeslat přihlašovací email. Zkuste se prosím přihlásit znovu.");
			return $response->withRedirect($this->router->pathFor('landing'));
		}
	})->setName('signup');
	
	
	$this->get("/signupSuccess", function (Request $request, Response $response, array $args) {
		return $this->view->render($response, 'signupSuccess.twig', []);
	})->setName('signupSuccess');
	
	
	$this->get("/login", function (Request $request, Response $response, array $args) {
		return $this->view->render($response, 'loginScreen.twig', []);
	})->setName('loginAskEmail');
	
	
	$this->post("/login", function (Request $request, Response $response, array $args) {
		$email = $request->getParam('email');
		if ($this->userService->isEmailExisting($email)) {
			try {
				$this->userService->sendLoginTokenByMail($email);
			} catch (Exception $e) {
				$this->logger->addError("Error sending login email to $email with token ".
					$this->userService->getTokenForEmail($email), array($e));
				$this->flashMessages->error("Nezdařilo se odeslat přihlašovací email. Zkus to prosím znovu.");
				return $response->withRedirect($this->router->pathFor('loginScreen'));
			}
			
			$this->flashMessages->success('Posláno! Klikni na link v mailu a tím se přihlásíš!');
			return $response->withRedirect($this->router->pathFor('loginScreenAfterSent'));
			
		} else {
			$this->flashMessages->error('Pardon, tvůj přihlašovací email tu nemáme. Nechceš se spíš zaregistrovat?');
			return $response->withRedirect($this->router->pathFor('landing'));
		}
		
	})->setName('loginScreenAfterSent');
	
	
	$this->get('/loginScreenAfterSend', function (Request $request, Response $response, array $args) {
		return $this->view->render($response, 'loginScreenAfterSend.twig', []);
	})->setName('loginScreenAfterSent');
	
	
	$this->get("/login/{token}", function (Request $request, Response $response, array $args) {
		$loginToken = $args['token'];
		if ($this->userService->isLoginTokenValid($loginToken)) {
			$user = $this->userService->getUserFromToken($loginToken);
			$this->userService->saveUserIdIntoSession($user);
			
			return $response->withRedirect($this->router->pathFor('getDashboard'));
		} else {
			$this->flashMessages->warning('Token není platný. Nech si prosím poslat nový přihlašovací email.');
			return $response->withRedirect($this->router->pathFor('loginAskEmail'));
		}
	})->setName('loginWithToken');
	
	
	$this->get("/logout", function (Request $request, Response $response, array $args) {
		$this->userService->logoutUser();
		$this->flashMessages->info('Jsi úspěšně odhlášený');
		
		return $response->withRedirect($this->router->pathFor('landing'));
	})->setName('logout');
	
	
	$this->get("/dashboard", function (Request $request, Response $response, array $args) {
		$role = $this->userService->getRole($request->getAttribute('user'));
		if (is_null($role)) {
			$this->flashMessages->error('Sorry, you are not logged');
			return $response->withRedirect($this->router->pathFor('loginScreen'));
		} else {
			if (!$this->userService->isUserRoleValid($role)) {
				throw new Exception('Unknown role "'.$role.'"');
			} else {
				switch ($role) {
					case 'patrol-leader': {
						return $response->withRedirect($this->router->pathFor('pl-dashboard'));
						break;
					}
					case 'ist': {
						return $response->withRedirect($this->router->pathFor('ist-dashboard'));
					}
					default: {
						throw new Exception('Non-implemented role "'.$role.'"!');
					}
				}
			}
		}
	})->setName('getDashboard');
	
	
	// PATROLS
	
	$this->group("/patrol-leader", function () {
		
		// PATROL-LEADER AREA
		
		$this->get("/dashboard", function (Request $request, Response $response, array $args) {
			$pl = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
			return $this->view->render($response, 'dashboard-pl.twig', ['plInfo' => $pl]);
		})->setName('pl-dashboard');
		
		$this->get("/details", function (Request $request, Response $response, array $args) {
			return $this->renderer->render($response, 'patrol-leader-details.html', $args);
		});
		
		$this->post("/details", function (Request $request, Response $response, array $args) {
			// TODO process
			return $response->withRedirect("TODO");
		});
		
		$this->get("/addParticipant", function (Request $request, Response $response, array $args) {
			return $this->renderer->render($response, 'addParticipant-patrol.html', $args);
		});
		
		$this->post("/addParticipant", function (Request $request, Response $response, array $args) {
			// TODO process
			return $response->withRedirect($this->router->pathFor('pl-dashboard'));
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
		
	})->add(function (Request $request, Response $response, callable $next) {
		// protected area for Patrol Leaders
		if ($this->userService->getRole($request->getAttribute('user')) != 'patrol-leader') {
			$this->flashMessages('Sorry, you are not logged as Patrol Leader');
			return $response->withRedirect($this->router->pathFor('loginScreen'));
		} else {
			$response = $next($request, $response);
			return $response;
		}
	});
	
	// IST
	
	$this->group("/ist", function () {
		
		$this->get("/dashboard", function (Request $request, Response $response, array $args) {
			return $this->view->render($response, 'dashboard-ist.twig', $args);
		})->setName('ist-dashboard');
		
		$this->get("/details[/{id}]", function (Request $request, Response $response, array $args) {
			return $this->view->render($response, 'participant-details.html', $args);
		});
		
		$this->post("/details[/{id}]", function (Request $request, Response $response, array $args) {
			// TODO process
			return $response->withRedirect("TODO");
		});
		
	})->add(function (Request $request, Response $response, callable $next) {
		// protected area for Patrol Leaders
		if ($this->userService->getRole($request->getAttribute('user')) != 'ist') {
			$this->flashMessages->error('Sorry, you are not logged as IST');
			return $response->withRedirect($this->router->pathFor('loginScreen'));
		} else {
			$response = $next($request, $response);
			return $response;
		}
	});
	
	// GUESTS
	// TODO
	
	// ADMINISTRATION
	
	$this->any("/admin", function (Request $request, Response $response, array $args) {
		global $adminerSettings;
		$adminerSettings = $this->get('settings')['adminer'];
		require __DIR__."/../admin/custom.php";
	});
	
	// LANDING PAGE
	
	$this->get("", function (Request $request, Response $response, array $args) {
		$this->flashMessages->info('Welcome!');
		
		return $this->view->render($response, 'landing-page.twig', ['router' => $this->router]);
	})->setName("landing");
});
