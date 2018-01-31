<?php

use Slim\Http\Request;
use Slim\Http\Response;

$check['nonLoggedOnly'] = function (Request $request, Response $response, callable $next) {
	// protected area for non-logged users only
	if (is_null($request->getAttribute('user'))) {
		$response = $next($request, $response);
		return $response;
	} else {
		/** @var \kissj\Event\Event $event */
		if ($event = $request->getAttribute('event')) {
			return $response->withRedirect($this->router->pathFor('getDashboard', ['eventSlug' => $event->slug]));
		} else {
			return $response->withRedirect($this->router->pathFor('createEvent'));
		}
	}
};

$check['loggedOnly'] = function (Request $request, Response $response, callable $next) {
	// protected area for logged users only
	if (is_null($request->getAttribute('user'))) {
		$this->flashMessages->warning('Pardon, ale nejsi přihlášený. Přihlaš se prosím zadáním emailu');
		/** @var \kissj\Event\Event $event */
		if ($event = $request->getAttribute('event')) {
			return $response->withRedirect($this->router->pathFor('landing', ['eventSlug' => $event->slug]));
		} else {
			return $response->withRedirect($this->router->pathFor('kissj-landing'));
		}
	} else {
		$response = $next($request, $response);
		return $response;
	}
};

$check['addRoleEventInfoIntoRequest'] = function (Request $request, Response $response, callable $next) {
	if (!is_null($request->getAttribute('user'))) {
		// add interesting info about logged role and current event from router into request
		
		/** @var \kissj\Event\Event $event */
		$event = $this->get('eventService')->getEventFromSlug($request->getAttribute('route')->getArguments()['eventSlug']);
		$request = $request->withAttribute('event', $event);
		
		// TODO continue
		
		/** @var \kissj\User\RoleRepository $roleRepository */
		$roleRepository = $this->get('roleRepository');
		/** @var \kissj\User\RoleService $roleService */
		$roleService = $this->get('roleService');
		/** @var \kissj\User\User $user */
		$user = $request->getAttribute('user');
		
		if ($roleRepository->isExisting(['userId' => $user->id, 'event' => $event->id])) {
			$role = $roleService->getRole($request->getAttribute('user'));
			$request = $request->withAttribute('role', $roleRepository->findOneBy(['userId' => $user->id])
			);
		} else {
			$request = $request->withAttribute('role', null);
		}
		/** @var \Slim\Views\Twig $view */
		$view = $this->get('view');
		$view->getEnvironment()->addGlobal('userRole', $role);
		$view->getEnvironment()->addGlobal('userCustomHelp', $roleService->getHelpForRole($role));
		$view->getEnvironment()->addGlobal('eventSlug', $event->slug);
	}
	
	$response = $next($request, $response);
	return $response;
};


/*

#    # ###### #####   ##
##  ## #        #    #  #
# ## # #####    #   #    #
#    # #        #   ######
#    # #        #   #    #
#    # ######   #   #    #

*/

$app->get("/", function (Request $request, Response $response, array $args) {
	return $response->withRedirect($this->router->pathFor('kissj-landing'));
});

// API VERSION

$app->group("/v1", function () use ($check) {
	
	$this->get("", function (Request $request, Response $response, array $args) {
		return $response->withRedirect($this->router->pathFor('kissj-landing'));
	});
	
	// LANGUAGE
	
	$this->group("/cs", function () use ($check) {
		
		$this->get("", function (Request $request, Response $response, array $args) {
			return $response->withRedirect($this->router->pathFor('kissj-landing'));
		});
		
		/*
		
		 ####  #   #  ####  ##### ###### #    #
		#       # #  #        #   #      ##  ##
		 ####    #    ####    #   #####  # ## #
			 #   #        #   #   #      #    #
		#    #   #   #    #   #   #      #    #
		 ####    #    ####    #   ###### #    #
		
		*/
		
		$this->group("/kissj", function () use ($check) {
			
			$this->get("", function (Request $request, Response $response, array $args) {
				return $this->view->render($response, 'kissj/landing.twig');
			})->setName('kissj-landing');
			
			
			$this->get('/loginHelp', function (Request $request, Response $response, array $args) {
				return $this->view->render($response, 'kissj/loginHelp.twig');
			})->setName('kissj-loginHelp')->add($check['nonLoggedOnly']);
			
			// non-logged users only
			$this->group("", function () use ($check) {
				
				/*
		
				#    #  ####  ###### #####
				#    # #      #      #    #
				#    #  ####  #####  #    #
				#    #      # #      #####
				#    # #    # #      #   #
				 ####   ####  ###### #    #
				
				*/
				
				
				$this->post("/trySignup", function (Request $request, Response $response, array $args) {
					$parameters = $request->getParsedBody();
					$email = $parameters['email'];
					
					if ($this->userService->isEmailExisting($email)) {
						$this->flashMessages->error('Nepovedlo se založit uživatele pro email '.htmlspecialchars($email, ENT_QUOTES).', protože už takový existuje. Nechceš se spíš přihlásit?');
						if (isset($parameters['eventSlug'])) {
							$pathForRedirect = $this->router->pathFor('landing', ['eventSlug' => $parameters['eventSlug']]);
						} else {
							$pathForRedirect = $this->router->pathFor('kissj-landing');
						}
						return $response->withRedirect($pathForRedirect);
					}
					
					$user = $this->userService->registerUser($email);
					$this->logger->info('Created new user with email '.$email);
					
					if (isset($parameters['role'], $parameters['eventSlug'])) {
						// participant signup
						
						$role = $parameters['role'];
						if (!$this->roleService->isUserRoleNameValid($role)) {
							throw new Exception('User role "'.$role.'" is not valid');
						}
						
						$this->roleService->addRole($user, $role);
						/** @var kissj\Event\Event $event */
						$event = $this->eventService->getEventFromSlug($parameters['eventSlug']);
						try {
							$this->userService->sendLoginTokenByMail(
								$email,
								$this->roleService->getReadableRoleName($role),
								$event->readableNameLong);
							return $response->withRedirect($this->router->pathFor('signupSuccess'));
						} catch (Exception $e) {
							$this->logger->addError("Error sending registration email to $email to event $event->slug with token ".$this->userService->getTokenForEmail($email), array($e));
							$this->flashMessages->error("Registrace se povedla, ale nezdařilo se odeslat přihlašovací email. Zkus se prosím přihlásit znovu.");
							return $response->withRedirect($this->router->pathFor('landing', ['eventSlug' => $event->slug]));
						}
					} else {
						// new event registration signup
						try {
							$this->userService->sendLoginTokenByMail($email);
							return $response->withRedirect($this->router->pathFor('kissj-signupSuccess'));
						} catch (Exception $e) {
							$this->logger->addError("Error sending registration email to $email with token ".$this->userService->getTokenForEmail($email), array($e));
							$this->flashMessages->error("Registrace se povedla, ale nezdařilo se odeslat přihlašovací email )-:");
							return $response->withRedirect($this->router->pathFor('kissj-landing'));
						}
						
					}
				})->setName('trySignup');
				
				
				$this->get("/signupSuccess", function (Request $request, Response $response, array $args) {
					$this->flashMessages->success('Úspěšně zadána emailová adresa!');
					return $this->view->render($response, 'signupSuccess.twig');
				})->setName('signupSuccess');
				
				
				$this->get("/registrationSignupSuccess", function (Request $request, Response $response, array $args) {
					$this->flashMessages->success('Úspěšně zadána emailová adresa!');
					return $this->view->render($response, 'kissj/signupSuccess.twig');
				})->setName('kissj-signupSuccess');
				
				
				$this->get("/login/{token}[/{eventSlug}]", function (Request $request, Response $response, array $args) {
					$loginToken = $args['token'];
					if ($this->userService->isLoginTokenValid($loginToken)) {
						$user = $this->userService->getUserFromToken($loginToken);
						$this->userRegeneration->saveUserIdIntoSession($user);
						$this->userService->invalidateAllLoginTokens($user);
						if (isset($args['eventSlug'])) {
							return $response->withRedirect($this->router->pathFor('getDashboard', ['eventSlug' => $args['eventSlug']]));
						} else {
							return $response->withRedirect($this->router->pathFor('createEvent'));
						}
					} else {
						$this->flashMessages->warning('Token pro přihlášení není platný. Nech si prosím poslat nový přihlašovací email.');
						if (isset($args['eventSlug'])) {
							return $response->withRedirect($this->router->pathFor('loginAskEmail'));
						} else {
							return $response->withRedirect($this->router->pathFor('kissj-landing'));
						}
					}
				})->setName('loginWithToken');
				
			})->add($check['nonLoggedOnly']);
			
			
			$this->get("/logout", function (Request $request, Response $response, array $args) {
				$this->userService->logoutUser();
				$this->flashMessages->info('Odhlášení bylo úspěšné');
				
				/** @var \kissj\Event\Event $event */
				if ($event = $request->getAttribute('event')) {
					$pathForRedirect = $this->router->pathFor('landing', ['eventSlug' => $event->slug]);
				} else {
					$pathForRedirect = $this->router->pathFor('kissj-landing');
				}
				
				return $response->withRedirect($pathForRedirect);
			})->setName('logout')->add($check['loggedOnly']);
			
			
			$this->get("/createEvent", function (Request $request, Response $response, array $args) {
				$banks = [
					['name' => 'FioBanka', 'id' => 222],
					['name' => 'Komerční banka', 'id' => 111],
				];
				return $this->view->render($response, 'kissj/createEvent.twig', ['banks' => $banks]);
			})->setName('createEvent')->add($check['loggedOnly']);
			
			
			$this->post("/createEvent", function (Request $request, Response $response, array $args) {
				$params = $request->getParams();
				if ($this->eventService->isEventDetailsValid(
					$params['slug'] ?? null,
					$params['readableName'] ?? null,
					$params['accountNumber'] ?? null,
					$params['prefixVariableSymbol'] ?? null,
					$params['automaticPaymentPairing'] ?? null,
					$params['bankId'] ?? null,
					$params['bankApi'] ?? null,
					$params['allowPatrols'] ?? null,
					$params['maximalClosedPatrolsCount'] ?? null,
					$params['minimalPatrolParticipantsCount'] ?? null,
					$params['maximalPatrolParticipantsCount'] ?? null,
					$params['allowIsts'] ?? null,
					$params['maximalClosedIstsCount'] ?? null)) {
					
					/** @var \kissj\Event\Event $newEvent */
					$newEvent = $this->eventService->createEvent(
						$params['slug'] ?? null,
						$params['readableName'] ?? null,
						$params['accountNumber'] ?? null,
						$params['prefixVariableSymbol'] ?? null,
						$params['automaticPaymentPairing'] ?? null,
						$params['bankId'] ?? null,
						$params['bankApi'] ?? null,
						$params['allowPatrols'] ?? null,
						$params['maximalClosedPatrolsCount'] ?? null,
						$params['minimalPatrolParticipantsCount'] ?? null,
						$params['maximalPatrolParticipantsCount'] ?? null,
						$params['allowIsts'] ?? null,
						$params['maximalClosedIstsCount'] ?? null);
					
					$this->flashMessages->success('Registrace je úspěšně vytvořená!');
					$this->logger->info('Created event with ID '.$newEvent->id.' and slug '.$newEvent->slug);
					
					return $response->withRedirect($this->router->pathFor('getDashboard', ['eventSlug' => $newEvent->slug]));
				} else {
					$this->flashMessages->warning('Některé údaje nebyly validní - prosím zkus úpravu údajů znovu.');
					return $response->withRedirect($this->router->pathFor('createEvent'));
				}
				// TODO add event-admins (roles table?)
			})->setName('postCreateEvent')->add($check['loggedOnly']);
			
			// from events only - TODO shift that fcs down
			
			$this->get("/login", function (Request $request, Response $response, array $args) {
				return $this->view->render($response, 'kissj/loginScreen.twig', ['eventSlug' => 'cej2018']);
			})->setName('loginAskEmail')->add($check['nonLoggedOnly']);
			
			
			$this->post("/login", function (Request $request, Response $response, array $args) {
				$email = $request->getParam('email');
				if ($this->userService->isEmailExisting($email)) {
					$user = $this->userService->getUserFromEmail($email);
					/** @var \kissj\User\Role $role */
					$role = $this->roleService->getRole($user);
					$readableRole = $this->roleService->getReadableRoleName($role->name);
					try {
						$this->userService->sendLoginTokenByMail($email, $readableRole);
					} catch (Exception $e) {
						$this->logger->addError("Error sending login email to $email with token ".
							$this->userService->getTokenForEmail($email), array($e));
						$this->flashMessages->error("Nezdařilo se odeslat přihlašovací email. Zkus to prosím znovu.");
						return $response->withRedirect($this->router->pathFor('loginAskEmail'));
					}
					
					$this->flashMessages->success('Posláno! Klikni na odkaz v mailu a tím se přihlásíš!');
					return $response->withRedirect($this->router->pathFor('landing'));
					
				} else {
					$this->flashMessages->error('Pardon, tvůj přihlašovací email tu nemáme. Nechceš se spíš zaregistrovat?');
					return $response->withRedirect($this->router->pathFor('landing'));
				}
				
			})->setName('loginScreenAfterSent')->add($check['nonLoggedOnly']);
			
			
			$this->get("/registration/{eventSlug}/{role}", function (Request $request, Response $response, array $args) {
				$role = $args['role'];
				if (!$this->roleService->isUserRoleNameValid($role)) {
					throw new Exception('User role "'.$role.'" is not valid');
				}
				
				/** @var kissj\Event\Event $event */
				$event = $this->eventService->getEventFromSlug($args['event']);
				
				return $this->view->render($response, 'registration.twig', [
					'role' => $role,
					'readableRole' => $this->roleService->getReadableRoleName($role),
					'eventSlug' => $event->slug,
					'eventReadableName' => $event->readableNameLong,
				]);
			})->setName('registration')->add($check['nonLoggedOnly']);
			
		});
		
		/*
		
		###### #    # ###### #    # #####
		#      #    # #      ##   #   #
		#####  #    # #####  # #  #   #
		#      #    # #      #  # #   #
		#       #  #  #      #   ##   #
		######   ##   ###### #    #   #
		
		*/
		
		$this->group("/event/{eventSlug}", function () use ($check) {
			
			/*
			
			 #        ##   #    # #####  # #    #  ####
			 #       #  #  ##   # #    # # ##   # #    #
			 #      #    # # #  # #    # # # #  # #
			 #      ###### #  # # #    # # #  # # #  ###
			 #      #    # #   ## #    # # #   ## #    #
			 ###### #    # #    # #####  # #    #  ####
			 
			*/
			
			$this->get("", function (Request $request, Response $response, array $args) {
				return $this->view->render($response, 'landing-page.twig');
			})->setName("landing")->add($check['nonLoggedOnly']);
			
			$this->get("/registrationLostSoul", function (Request $request, Response $response, array $args) {
				if ($this->get('settings')['useTestingSite']) {
					$this->flashMessages->success('Úspěch!');
					$this->flashMessages->warning('Pozor!');
					$this->flashMessages->error('Něco se pokazilo!');
					$this->flashMessages->info('Informace.');
				}
				
				return $this->view->render($response, 'registrationLostSoul.twig');
			})->setName('registrationLostSoul')->add($check['nonLoggedOnly']);
			
			// logged users only
			$this->group("", function () use ($check) {
				
				$this->get("/getDashboard", function (Request $request, Response $response, array $args) {
					$roleName = $this->roleService->getRole($request->getAttribute('user'))->name;
					if (!$this->roleService->isUserRoleNameValid($roleName)) {
						throw new Exception('Unknown role "'.$roleName.'"');
					} else {
						switch ($roleName) {
							case 'patrol-leader':
								{
									return $response->withRedirect($this->router->pathFor('pl-dashboard'));
								}
							case 'ist':
								{
									return $response->withRedirect($this->router->pathFor('ist-dashboard'));
								}
							case 'admin':
								{
									return $response->withRedirect($this->router->pathFor('admin-dashboard'));
								}
							default:
								{
									throw new Exception('Non-implemented role "'.$roleName.'"!');
								}
						}
					}
					
				})->setName('getDashboard');
				
				/*
				
				#####    ##   ##### #####   ####  #
				#    #  #  #    #   #    # #    # #
				#    # #    #   #   #    # #    # #
				#####  ######   #   #####  #    # #
				#      #    #   #   #   #  #    # #
				#      #    #   #   #    #  ####  ######
				
				*/
				
				$this->group("/patrol", function () {
					
					// PATROL-LEADER
					
					$this->get("/dashboard", function (Request $request, Response $response, array $args) {
						$user = $request->getAttribute('user');
						$patrolLeader = $this->patrolService->getPatrolLeader($user);
						$allParticipants = $this->patrolService->getAllParticipantsBelongsPatrolLeader($patrolLeader);
						$onePayment = $this->patrolService->getOnePayment($patrolLeader);
						return $this->view->render($response, 'dashboard-pl.twig', ['user' => $user, 'plDetails' => $patrolLeader, 'allPDetails' => $allParticipants, 'payment' => $onePayment]);
					})->setName('pl-dashboard');
					
					// excluded from status registration only
					// TODO rethink about more elegant solution
					$this->get("/participant/{participantId}/showDetails", function (Request $request, Response $response, array $args) {
						$pDetails = $this->patrolService->getPatrolParticipant($args['participantId']);
						return $this->view->render($response, 'showDetails-p.twig', ['pDetails' => $pDetails]);
					})->setName('p-showDetails')->add(function (Request $request, Response $response, callable $next) {
						// participants actions are allowed only for their Patrol Leader
						$routeParams = $request->getAttribute('routeInfo')[2]; // get route params from request (undocumented feature)
						if (!$this->patrolService->patrolParticipantBelongsPatrolLeader(
							$this->patrolService->getPatrolParticipant($routeParams['participantId']),
							$this->patrolService->getPatrolLeader($request->getAttribute('user')))) {
							
							$this->flashMessages->error('Bohužel, nemůžeš provádět akce s účastníky, které neregistruješ ty.');
							return $response->withRedirect($this->router->pathFor('pl-dashboard'));
						} else {
							$response = $next($request, $response);
							return $response;
						}
					});
					
					// open status registration only
					$this->group("", function () {
						
						$this->get("/changeDetails", function (Request $request, Response $response, array $args) {
							$plDetails = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
							return $this->view->render($response, 'changeDetails-pl.twig', ['plInfo' => $plDetails]);
						})->setName('pl-changeDetails');
						
						$this->post("/postDetails", function (Request $request, Response $response, array $args) {
							$params = $request->getParams();
							if ($this->patrolService->isPatrolLeaderDetailsValid(
								$params['firstName'] ?? null,
								$params['lastName'] ?? null,
								$params['allergies'] ?? null,
								$params['birthDate'] ?? null,
								$params['birthPlace'] ?? null,
								$params['country'] ?? null,
								$params['gender'] ?? null,
								$params['permanentResidence'] ?? null,
								$params['scoutUnit'] ?? null,
								$params['telephoneNumber'] ?? null,
								$params['email'] ?? null,
								$params['foodPreferences'] ?? null,
								$params['cardPassportNumber'] ?? null,
								$params['notes'] ?? null,
								$params['patrolName'] ?? null)) {
								
								$patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
								$this->patrolService->editPatrolLeaderInfo(
									$patrolLeader,
									$params['firstName'] ?? null,
									$params['lastName'] ?? null,
									$params['allergies'] ?? null,
									$params['birthDate'] ?? null,
									$params['birthPlace'] ?? null,
									$params['country'] ?? null,
									$params['gender'] ?? null,
									$params['permanentResidence'] ?? null,
									$params['scoutUnit'] ?? null,
									$params['telephoneNumber'] ?? null,
									$params['email'] ?? null,
									$params['foodPreferences'] ?? null,
									$params['cardPassportNumber'] ?? null,
									$params['notes'] ?? null,
									$params['patrolName'] ?? null);
								
								$this->flashMessages->success('Údaje úspěšně uloženy');
								return $response->withRedirect($this->router->pathFor('pl-dashboard'));
							} else {
								$this->flashMessages->warning('Některé údaje nebyly validní - prosím zkus úpravu údajů znovu.');
								return $response->withRedirect($this->router->pathFor('pl-changeDetails'));
							}
						})->setName('pl-postDetails');
						
						$this->get("/closeRegistration", function (Request $request, Response $response, array $args) {
							$patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
							$validRegistration = $this->patrolService->isCloseRegistrationValid($patrolLeader); // call because of warnings
							if ($validRegistration) {
								return $this->view->render($response, 'closeRegistration-pl.twig');
							} else {
								return $response->withRedirect($this->router->pathFor('pl-dashboard'));
							}
						})->setName('pl-closeRegistration');
						
						$this->post("/confirmCloseRegistration", function (Request $request, Response $response, array $args) {
							$patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
							if ($this->patrolService->isCloseRegistrationValid($patrolLeader)) {
								$this->patrolService->closeRegistration($patrolLeader);
								$this->flashMessages->success('Registrace úspěšně uzavřena, čeká na schválení');
								$this->logger->info('Closing registration for PatrolLeader with ID '.$patrolLeader->id);
								return $response->withRedirect($this->router->pathFor('pl-dashboard'));
							} else {
								$this->flashMessages->error('Registraci ještě nelze uzavřít');
								return $response->withRedirect($this->router->pathFor('pl-dashboard'));
							}
						})->setName('pl-confirmCloseRegistration');
						
						// PARTICIPANT
						
						$this->get("/addParticipant", function (Request $request, Response $response, array $args) {
							// create participant and reroute to edit him
							$newParticipant = $this->patrolService->addPatrolParticipant($this->patrolService->getPatrolLeader($request->getAttribute('user')));
							return $response->withRedirect($this->router->pathFor('p-changeDetails', ['participantId' => $newParticipant->id]));
						})->setName('pl-addParticipant');
						
						$this->group("/participant/{participantId}", function () {
							
							$this->get("/changeDetails", function (Request $request, Response $response, array $args) {
								$pDetails = $this->patrolService->getPatrolParticipant($args['participantId']);
								return $this->view->render($response, 'changeDetails-p.twig', ['pDetails' => $pDetails]);
							})->setName('p-changeDetails');
							
							$this->post("/postDetails", function (Request $request, Response $response, array $args) {
								$params = $request->getParams();
								
								if ($this->patrolService->isPatrolParticipantDetailsValid(
									$params['firstName'] ?? null,
									$params['lastName'] ?? null,
									$params['allergies'] ?? null,
									$params['birthDate'] ?? null,
									$params['birthPlace'] ?? null,
									$params['country'] ?? null,
									$params['gender'] ?? null,
									$params['permanentResidence'] ?? null,
									$params['scoutUnit'] ?? null,
									$params['telephoneNumber'] ?? null,
									$params['email'] ?? null,
									$params['foodPreferences'] ?? null,
									$params['cardPassportNumber'] ?? null,
									$params['notes'] ?? null,
									$params['patrolName'] ?? null)) {
									
									$this->patrolService->editPatrolParticipant(
										$this->patrolService->getPatrolParticipant($args['participantId']),
										$params['firstName'] ?? null,
										$params['lastName'] ?? null,
										$params['allergies'] ?? null,
										$params['birthDate'] ?? null,
										$params['birthPlace'] ?? null,
										$params['country'] ?? null,
										$params['gender'] ?? null,
										$params['permanentResidence'] ?? null,
										$params['scoutUnit'] ?? null,
										$params['telephoneNumber'] ?? null,
										$params['email'] ?? null,
										$params['foodPreferences'] ?? null,
										$params['cardPassportNumber'] ?? null,
										$params['notes'] ?? null);
									
									$this->flashMessages->success('Účastník úspěšně uložen');
									return $response->withRedirect($this->router->pathFor('pl-dashboard'));
								} else {
									$this->flashMessages->warning('Některé údaje nebyly validní - prosím zkus přidat účastníka znovu.');
									return $response->withRedirect($this->router->pathFor('pl-addParticipant'));
								}
							})->setName('p-postDetails');
							
							$this->get("/delete", function (Request $request, Response $response, array $args) {
								$pDetails = $this->patrolService->getPatrolParticipant($args['participantId']);
								return $this->view->render($response, 'delete-p.twig', ['pDetail' => $pDetails]);
							})->setName('p-delete');
							
							$this->post("/confirmDelete", function (Request $request, Response $response, array $args) {
								$patrolParticipant = $this->patrolService->getPatrolParticipant($args['participantId']);
								$this->patrolService->deletePatrolParticipant($patrolParticipant);
								$this->flashMessages->success('Účastník úspěšně vymazán!');
								return $response->withRedirect($this->router->pathFor('pl-dashboard'));
							})->setName('p-confirmDelete');
							
						})->add(function (Request $request, Response $response, callable $next) {
							// participants actions are allowed only for their Patrol Leader
							$routeParams = $request->getAttribute('routeInfo')[2]; // get route params from request (undocumented feature)
							if (!$this->patrolService->patrolParticipantBelongsPatrolLeader(
								$this->patrolService->getPatrolParticipant($routeParams['participantId']),
								$this->patrolService->getPatrolLeader($request->getAttribute('user')))) {
								
								$this->flashMessages->error('Bohužel, nemůžeš provádět akce s účastníky, které neregistruješ ty.');
								return $response->withRedirect($this->router->pathFor('pl-dashboard'));
							} else {
								$response = $next($request, $response);
								return $response;
							}
						});
						
					})->add(function (Request $request, Response $response, callable $next) {
						// change data can only users with open registration
						/** @var \kissj\User\Role $role */
						$role = $request->getAttribute('role');
						if ($role->status != 'open') {
							$this->logger->warning('User '.$request->getAttribute('user')->email.' is trying to change data, even he has role "'.$role->name.'"');
							throw new Exception('Nemůžeš měnit údaje když nejsi ve stavu zadávání údajů!');
						}
						
						$response = $next($request, $response);
						return $response;
					});
					
				})->add(function (Request $request, Response $response, callable $next) {
					// protected area for Patrol Leaders
					if ($this->roleService->getRole($request->getAttribute('user'))->name != 'patrol-leader') {
						$this->flashMessages->error('Pardon, nejsi na akci přihlášený jako Patrol Leader');
						return $response->withRedirect($this->router->pathFor('loginAskEmail'));
					} else {
						$response = $next($request, $response);
						return $response;
					}
				});
				
				/*
				
				#  ####  #####
				# #        #
				#  ####    #
				#      #   #
				# #    #   #
				#  ####    #
				
				*/
				
				$this->group("/ist", function () {
					
					$this->get("/dashboard", function (Request $request, Response $response, array $args) {
						$user = $request->getAttribute('user');
						$ist = $this->istService->getIst($user);
						$possibleOnePayment = $this->istService->getOnePayment($ist);
						return $this->view->render($response, 'dashboard-ist.twig', ['user' => $user, 'istDetails' => $ist, 'payment' => $possibleOnePayment]);
					})->setName('ist-dashboard');
					
					// open registration status only
					$this->group("", function () {
						
						$this->get("/changeDetails", function (Request $request, Response $response, array $args) {
							$istDetails = $this->istService->getIst($request->getAttribute('user'));
							return $this->view->render($response, 'changeDetails-ist.twig', ['istDetails' => $istDetails]);
						})->setName('ist-changeDetails');
						
						$this->post("/postDetails", function (Request $request, Response $response, array $args) {
							$params = $request->getParams();
							if ($this->istService->isIstDetailsValid(
								$params['firstName'] ?? null,
								$params['lastName'] ?? null,
								$params['allergies'] ?? null,
								$params['birthDate'] ?? null,
								$params['birthPlace'] ?? null,
								$params['country'] ?? null,
								$params['gender'] ?? null,
								$params['permanentResidence'] ?? null,
								$params['scoutUnit'] ?? null,
								$params['telephoneNumber'] ?? null,
								$params['email'] ?? null,
								$params['foodPreferences'] ?? null,
								$params['cardPassportNumber'] ?? null,
								$params['notes'] ?? null,
								
								$params['workPreferences'] ?? null,
								$params['skills'] ?? null,
								$params['languages'] ?? null,
								$params['arrivalDate'] ?? null,
								$params['leavingDate'] ?? null,
								$params['carRegistrationPlate'] ?? null)) {
								
								$this->istService->editIstInfo(
									$this->istService->getIst($request->getAttribute('user')),
									$params['firstName'] ?? null,
									$params['lastName'] ?? null,
									$params['allergies'] ?? null,
									$params['birthDate'] ?? null,
									$params['birthPlace'] ?? null,
									$params['country'] ?? null,
									$params['gender'] ?? null,
									$params['permanentResidence'] ?? null,
									$params['scoutUnit'] ?? null,
									$params['telephoneNumber'] ?? null,
									$params['email'] ?? null,
									$params['foodPreferences'] ?? null,
									$params['cardPassportNumber'] ?? null,
									$params['notes'] ?? null,
									
									$params['workPreferences'] ?? null,
									$params['skills'] ?? null,
									$params['languages'] ?? null,
									$params['arrivalDate'] ?? null,
									$params['leavingDate'] ?? null,
									$params['carRegistrationPlate'] ?? null);
								
								$this->flashMessages->success('Údaje úspěšně uloženy');
								return $response->withRedirect($this->router->pathFor('ist-dashboard'));
							} else {
								$this->flashMessages->warning('Některé údaje nebyly validní - prosím zkus úpravu údajů znovu.');
								return $response->withRedirect($this->router->pathFor('ist-changeDetails'));
							}
						})->setName('ist-postDetails');
						
						$this->get("/closeRegistration", function (Request $request, Response $response, array $args) {
							$ist = $this->istService->getIst($request->getAttribute('user'));
							$validRegistration = $this->istService->isCloseRegistrationValid($ist); // call because of warnings
							if ($validRegistration) {
								return $this->view->render($response, 'closeRegistration-ist.twig');
							} else {
								return $response->withRedirect($this->router->pathFor('ist-dashboard'));
							}
						})->setName('ist-closeRegistration');
						
						$this->post("/confirmCloseRegistration", function (Request $request, Response $response, array $args) {
							$ist = $this->istService->getIst($request->getAttribute('user'));
							if ($this->istService->isCloseRegistrationValid($ist)) {
								$this->istService->closeRegistration($ist);
								$this->flashMessages->success('Registrace úspěšně uzavřena, čeká na schválení');
								$this->logger->info('Closing registration for IST with ID '.$ist->id);
								return $response->withRedirect($this->router->pathFor('ist-dashboard'));
							} else {
								$this->flashMessages->error('Registraci ještě nelze uzavřít');
								return $response->withRedirect($this->router->pathFor('ist-dashboard'));
							}
						})->setName('ist-confirmCloseRegistration');
						
					})->add(function (Request $request, Response $response, callable $next) {
						// change data can only users with open registration
						/** @var \kissj\User\Role $role */
						$role = $request->getAttribute('role');
						if ($role->status != 'open') {
							$this->logger->warning('User '.$request->getAttribute('user')->email.' is trying to change data, even he has role "'.$role->name.'"');
							throw new Exception('You cannot change data if you have not opened registration!');
						}
						
						$response = $next($request, $response);
						return $response;
					});
					
				})->add(function (Request $request, Response $response, callable $next) {
					// protected area for IST
					if ($this->roleService->getRole($request->getAttribute('user'))->name != 'ist') {
						$this->flashMessages->error('Pardon, nejsi na akci přihlášený jako IST');
						return $response->withRedirect($this->router->pathFor('loginAskEmail'));
					} else {
						$response = $next($request, $response);
						return $response;
					}
				});
				
				/*
				  
				  ##   #####  #    # # #    #
				 #  #  #    # ##  ## # ##   #
				#    # #    # # ## # # # #  #
				###### #    # #    # # #  # #
				#    # #    # #    # # #   ##
				#    # #####  #    # # #    #
				
				*/
				
				$this->group("/admin", function () {
					
					$this->get("/dashboard", function (Request $request, Response $response, array $args) {
						$patrolStatistics = $this->get('patrolService')->getAllPatrolsStatistics();
						$istStatistics = $this->get('istService')->getAllIstsStatistics();
						
						return $this->view->render($response, 'admin/dashboard-admin.twig', ['eventName' => 'CEJ 2018', 'patrols' => $patrolStatistics, 'ists' => $istStatistics]);
					})->setName('admin-dashboard');
					
					// APPROVING
					$this->group("/approving", function () {
						
						$this->get("", function (Request $request, Response $response, array $args) {
							$closedPatrols = $this->patrolService->getAllClosedPatrols();
							$closedIsts = $this->istService->getAllClosedIsts();
							
							return $this->view->render($response, 'admin/approving-admin.twig', ['eventName' => 'CEJ 2018', 'closedPatrols' => $closedPatrols, 'closedIsts' => $closedIsts]);
						})->setName('admin-approving');
						
						$this->post("/approvePatrolLeader/{patrolLeaderId}", function (Request $request, Response $response, array $args) {
							/** @var \kissj\Participant\Patrol\PatrolService $patrolService */
							$patrolService = $this->patrolService;
							$patrolLeader = $patrolService->getPatrolLeaderFromId($args['patrolLeaderId']);
							$patrolService->approvePatrol($patrolLeader);
							$role = $this->roleService->getRole($patrolLeader->user);
							$payment = $this->paymentService->createNewPayment($role);
							$patrolService->sendPaymentByMail($payment, $patrolLeader);
							$this->flashMessages->success('Patrola schválena, platba vygenerována a mail odeslán');
							$this->logger->info('Approved registration for Patrol Leader with ID '.$patrolLeader->id);
							
							return $response->withRedirect($this->router->pathFor('admin-approving'));
						})->setName('approvePatrolLeader');
						
						$this->get("/openPatrolLeader/{patrolLeaderId}", function (Request $request, Response $response, array $args) {
							$patrolLeader = $this->patrolService->getPatrolLeaderFromId($args['patrolLeaderId']);
							return $this->view->render($response, 'admin/openPatrolLeader.twig', ['patrolLeader' => $patrolLeader]);
						})->setName('openPatrolLeader');
						
						$this->post("/openPatrolLeader/{patrolLeaderId}", function (Request $request, Response $response, array $args) {
							$patrolLeader = $this->patrolService->getPatrolLeaderFromId($args['patrolLeaderId']);
							$this->patrolService->openPatrol($patrolLeader);
							$reason = $request->getParsedBodyParam('reason');
							$this->patrolService->sendDenialMail($patrolLeader, $reason);
							$this->flashMessages->info('Patrola zamítnuta, email o zamítnutí poslán Patrol Leaderovi');
							$this->logger->info('Denied registration for Patrol Leader with ID '.$patrolLeader->id.' with reason: '.$reason);
							
							return $response->withRedirect($this->router->pathFor('admin-approving'));
						})->setName('openPatrolLeaderConfirmed');
						
						
						$this->post("/approveIst/{istId}", function (Request $request, Response $response, array $args) {
							/** @var \kissj\Participant\Ist\IstService $istService */
							$istService = $this->istService;
							$ist = $istService->getIstFromId($args['istId']);
							$istService->approveIst($ist);
							$role = $this->roleService->getRole($ist->user);
							$payment = $this->paymentService->createNewPayment($role);
							$istService->sendPaymentByMail($payment, $ist);
							$this->flashMessages->success('Člen IST schválen, platba vygenerována a mail odeslán');
							$this->logger->info('Approved registration for IST with ID '.$ist->id);
							
							return $response->withRedirect($this->router->pathFor('admin-approving'));
						})->setName('approveIst');
						
						$this->get("/openIst/{istId}", function (Request $request, Response $response, array $args) {
							$ist = $this->istService->getIstFromId($args['istId']);
							return $this->view->render($response, 'admin/openIst.twig', ['ist' => $ist]);
						})->setName('openIst');
						
						$this->post("/openIst/{istId}", function (Request $request, Response $response, array $args) {
							$ist = $this->istService->getIstFromId($args['istId']);
							$this->istService->openIst($ist);
							$reason = $request->getParsedBodyParam('reason');
							$this->istService->sendDenialMail($ist, $reason);
							$this->flashMessages->info('Člen IST zamítnut, email o zamítnutí poslán');
							$this->logger->info('Denied registration for IST with ID '.$ist->id.' with reason: '.$reason);
							
							return $response->withRedirect($this->router->pathFor('admin-approving'));
						})->setName('openIstConfirmed');
					});
					
					// PAYMENTS
					
					$this->group("/payments", function () {
						
						$this->get("", function (Request $request, Response $response, array $args) {
							$approvedPatrols = $this->patrolService->getAllApprovedPatrolsWithPayment();
							$approvedIsts = $this->istService->getAllApprovedIstsWithPayment();
							
							$this->view->render($response, 'admin/payments-admin.twig', ['eventName' => 'CEJ 2018', 'approvedPatrols' => $approvedPatrols, 'approvedIsts' => $approvedIsts]);
						})->setName('admin-payments');
						
						$this->post("/setPaymentPaid/{payment}", function (Request $request, Response $response, array $args) {
							/** @var \kissj\Payment\PaymentService $paymentService */
							$paymentService = $this->get('paymentService');
							$paymentId = $args['payment'];
							$paymentService->setPaymentPaid($paymentService->getPaymentFromId($paymentId));
							$this->flashMessages->success('Platba je označená jako zaplacená, mail o zaplacení odeslaný');
							$this->logger->info('Payment with ID '.$paymentId.' is set as paid by hand');
							
							return $response->withRedirect($this->router->pathFor('admin-payments'));
						})->setName('setPaymentPaid');
					});
					
					// EXPORTS
					
					$this->group("/export", function () {
						
						$this->get("/medical", function (Request $request, Response $response, array $args) {
							$csvRows = $this->exportService->medicalDataToCSV('cej2018');
							$this->logger->info('Downloaded current medical data');
							
							return $this->exportService->createCSVresponse($response, $csvRows, 'cej2018_medical');
						})->setName('admin-export-medical');
						
						$this->get("/logistic", function (Request $request, Response $response, array $args) {
							$csvRows = $this->exportService->logisticDataPatrolsToCSV('cej2018');
							$this->logger->info('Downloaded current logistic data');
							
							return $this->exportService->createCSVresponse($response, $csvRows, 'cej2018_logistic');
						})->setName('admin-export-logistic');
						
						$this->get("/full", function (Request $request, Response $response, array $args) {
							$csvRows = $this->exportService->allRegistrationDataToCSV('cej2018');
							$this->logger->info('Downloaded full current data about participants');
							
							return $this->exportService->createCSVresponse($response, $csvRows, 'cej2018_full');
						})->setName('admin-export-full');
					});
					
				})->add(function (Request $request, Response $response, callable $next) {
					// protected area for Registration Admins only
					if ($this->roleService->getRole($request->getAttribute('user'))->name != 'admin') {
						$this->flashMessages->error('Pardon, nejsi na akci vedený jako admin');
						return $response->withRedirect($this->router->pathFor('loginAskEmail'));
					} else {
						$response = $next($request, $response);
						return $response;
					}
				});
				
			})->add($check['loggedOnly']);
			
			$this->any("/admin", function (Request $request, Response $response, array $args) {
				global $adminerSettings;
				$adminerSettings = $this->get('settings')['adminer'];
				require __DIR__."/../admin/custom.php";
			})->setName('administration');
			
		})->add($check['addRoleEventInfoIntoRequest']);
	});
});