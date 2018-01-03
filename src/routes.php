<?php

use League\Csv\Reader;
use League\Csv\Writer;
use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->group("/".$settings['settings']['eventName'], function () {
	
	// non-logged users only
	$this->group("", function () {
		
		// LANDING, LEGAL & RANDOM
		
		$this->get("/registration/{role}", function (Request $request, Response $response, array $args) {
			$role = $args['role'];
			if (!$this->roleService->isUserRoleNameValid($role)) {
				throw new Exception('User role "'.$role.'" is not valid');
			}
			
			return $this->view->render($response, 'registration.twig', ['router' => $this->router, 'role' => $role, 'readableRole' => $this->roleService->getReadableRoleName($role)]);
		})->setName('registration');
		
		$this->get("/registrationLostSoul", function (Request $request, Response $response, array $args) {
			/* $this->flashMessages->success('Dostal jsi se sem!');
			$this->flashMessages->warning('Not done yet!');
			$this->flashMessages->error('Něco se pokazilo!');
			$this->flashMessages->info('Jsi krásný!'); */
			
			return $this->view->render($response, 'registrationLostSoul.twig');
		})->setName('registrationLostSoul');
		
		// REGISTRATION, LOGIN & LOGOUT
		
		$this->post("/signup/{role}", function (Request $request, Response $response, array $args) {
			$role = $args['role'];
			if (!$this->roleService->isUserRoleNameValid($role)) {
				throw new Exception('User role "'.$role.'" is not valid');
			}
			$email = $request->getParsedBodyParam("email");
			
			if ($this->userService->isEmailExisting($email)) {
				$this->flashMessages->error('Nepovedlo se založit uživatele pro email '.htmlspecialchars($email, ENT_QUOTES).', protože už takový existuje. Nechceš se spíš příhlásit?');
				return $response->withRedirect($this->router->pathFor('landing'));
			}
			
			$user = $this->userService->registerUser($email);
			
			$this->roleService->addRole($user, $role);
			$readableRole = $this->roleService->getReadableRoleName($role);
			try {
				$this->userService->sendLoginTokenByMail($email, $readableRole);
				return $response->withRedirect($this->router->pathFor('signupSuccess'));
			} catch (Exception $e) {
				$this->logger->addError("Error sending registration email to $email with token ".
					$this->userService->getTokenForEmail($email), array($e));
				$this->flashMessages->error("Registrace se povedla, ale nezdařilo se odeslat přihlašovací email. Zkus se prosím přihlásit znovu.");
				return $response->withRedirect($this->router->pathFor('landing'));
			}
		})->setName('signup');
		
		
		$this->get("/signupSuccess", function (Request $request, Response $response, array $args) {
			$this->flashMessages->success('Úspěšně zadána emailová adresa!');
			return $this->view->render($response, 'signupSuccess.twig', []);
		})->setName('signupSuccess');
		
		
		$this->get("/login", function (Request $request, Response $response, array $args) {
			return $this->view->render($response, 'loginScreen.twig', []);
		})->setName('loginAskEmail');
		
		
		$this->post("/login", function (Request $request, Response $response, array $args) {
			$email = $request->getParam('email');
			if ($this->userService->isEmailExisting($email)) {
				try {
					// TODO refactor
					$user = $this->userService->getUserFromEmail($email);
					$role = $this->roleService->getRole($user);
					$readableRole = $this->roleService->getReadableRoleName($role->name);
					
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
			
		})->setName('loginScreenAfterSent');
		
		
		$this->get('/loginHelp', function (Request $request, Response $response, array $args) {
			return $this->view->render($response, 'loginHelp.twig', []);
		})->setName('loginHelp');
		
		
		$this->get("/login/{token}", function (Request $request, Response $response, array $args) {
			$loginToken = $args['token'];
			if ($this->userService->isLoginTokenValid($loginToken)) {
				$user = $this->userService->getUserFromToken($loginToken);
				$this->userRegeneration->saveUserIdIntoSession($user);
				$this->userService->invalidateAllLoginTokens($user);
				
				return $response->withRedirect($this->router->pathFor('getDashboard'));
			} else {
				$this->flashMessages->warning('Token není platný. Nech si prosím poslat nový přihlašovací email.');
				return $response->withRedirect($this->router->pathFor('loginAskEmail'));
			}
		})->setName('loginWithToken');
		
	})->add(function (Request $request, Response $response, callable $next) {
		// protected area for non-logged users only
		if (is_null($this->roleService->getRole($request->getAttribute('user')))) {
			$response = $next($request, $response);
			return $response;
		} else {
			return $response->withRedirect($this->router->pathFor('getDashboard'));
		}
	});
	
	// logged users only
	$this->group("", function () {
		
		$this->get("/logout", function (Request $request, Response $response, array $args) {
			$this->userService->logoutUser();
			$this->flashMessages->info('Odhlášení bylo úspěšné');
			
			return $response->withRedirect($this->router->pathFor('landing'));
		})->setName('logout');
		
		
		$this->get("/getDashboard", function (Request $request, Response $response, array $args) {
			
			if (is_null($request->getAttribute('user'))) {
				$this->flashMessages->error('Sorry, you are not logged');
				return $response->withRedirect($this->router->pathFor('loginAskEmail'));
			}
			
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
		
		
		// PATROL
		
		$this->group("/patrol", function () {
			
			// PATROL-LEADER
			
			$this->get("/dashboard", function (Request $request, Response $response, array $args) {
				$user = $request->getAttribute('user');
				$patrolLeader = $this->patrolService->getPatrolLeader($user);
				$allParticipants = $this->patrolService->getAllParticipantsBelongsPatrolLeader($patrolLeader);
				return $this->view->render($response, 'dashboard-pl.twig', ['user' => $user, 'plDetails' => $patrolLeader, 'allPDetails' => $allParticipants]);
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
					$this->patrolService->isCloseRegistrationValid($patrolLeader); // call because of warnings
					return $this->view->render($response, 'closeRegistration-pl.twig');
				})->setName('pl-closeRegistration');
				
				$this->post("/confirmCloseRegistration", function (Request $request, Response $response, array $args) {
					$patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
					if ($this->patrolService->isCloseRegistrationValid($patrolLeader)) {
						$this->patrolService->closeRegistration($patrolLeader);
						$this->flashMessages->success('Registrace úspěšně uzavřena, čeká na schválení');
						$this->flashMessages->info('Registraci ti musíme schválit - jakmile se tak stane, pošleme ti email s platebními údaji');
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
					$this->get('logger')->warning('User '.$request->getAttribute('user')->email.' is trying to change data, even he has role "'.$role->name.'"');
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
		
		
		// IST
		
		$this->group("/ist", function () {
			
			$this->get("/dashboard", function (Request $request, Response $response, array $args) {
				$user = $request->getAttribute('user');
				$ist = $this->istService->getIst($user);
				return $this->view->render($response, 'dashboard-ist.twig', ['user' => $user, 'istDetails' => $ist]);
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
					$this->istService->isCloseRegistrationValid($ist); // call because of warnings
					return $this->view->render($response, 'closeRegistration-ist.twig');
				})->setName('ist-closeRegistration');
				
				$this->post("/confirmCloseRegistration", function (Request $request, Response $response, array $args) {
					$ist = $this->istService->getIst($request->getAttribute('user'));
					if ($this->istService->isCloseRegistrationValid($ist)) {
						$this->istService->closeRegistration($ist);
						$this->flashMessages->success('Registrace úspěšně uzavřena, čeká na schválení');
						$this->flashMessages->info('Registraci ti teď musíme schválit - jakmile se tak stane, pošleme ti email s platebními údaji');
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
					$this->get('logger')->warning('User '.$request->getAttribute('user')->email.' is trying to change data, even he has role "'.$role->name.'"');
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
		
		// ADMINISTRATION
		
		$this->group("/admin", function () {
			
			$this->get("/dashboard", function (Request $request, Response $response, array $args) {
				
				return $this->view->render($response, 'admin/dashboard-admin.twig', ['eventName' => 'CEJ 2018']);
			})->setName('admin-dashboard');
			
			$this->get("/approving", function (Request $request, Response $response, array $args) {
				$patrols = $this->patrolService->getAllClosedPatrols();
				$closedIsts = $this->istService->getAllClosedIsts();
				
				return $this->view->render($response, 'admin/approving-admin.twig', ['eventName' => 'CEJ 2018', 'patrols' => $patrols, 'closedIsts' => $closedIsts]);
			})->setName('admin-approving');
			
			$this->post("/approvePatrolLeader/{patrolLeaderId}", function (Request $request, Response $response, array $args) {
				$patrolLeaderId = $args['patrolLeaderId'];
				$this->patrolService->approvePatrol($patrolLeaderId);
				/** @var \kissj\Payment\PaymentService $paymentService */
				$paymentService = $this->paymentService;
				$role = $this->roleService->getRole($this->patrolService->getPatrolLeaderFromId($patrolLeaderId)->user);
				$payment = $paymentService->createNewPayment($role);
				$paymentService->sendPaymentByMail($payment);
				
				$this->flashMessages->success('Patrola schválena');
				
				return $response->withRedirect($this->router->pathFor('admin-approving'));
			})->setName('approvePatrolLeader');
			
			$this->get("/openPatrolLeader/{patrolLeaderId}", function (Request $request, Response $response, array $args) {
				$patrolLeader = $this->patrolService->getPatrolLeaderFromId($args['patrolLeaderId']);
				return $this->view->render($response, 'admin/openPatrolLeader.twig', ['patrolLeader' => $patrolLeader]);
			})->setName('openPatrolLeader');
			
			$this->get("/medical", function (Request $request, Response $response, array $args) {
				$data = $this->exportService->medicalDataToCSV('cej2018');

				$response->withHeader('Content-Type', 'text/csv')
					->withHeader('Expires', '0')
					->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
					->withHeader('Pragma', 'no-cache');

				$csv = Writer::createFromString('');
				$csv->setDelimiter(',');
				$csv->setOutputBOM(Reader::BOM_UTF8);
				$csv->insertAll($data);

				ob_start();
				$csv->output();
				$response->write(ob_get_clean());
				return $response;

			})->setName('admin-medical');
			
			$this->post("/openPatrolLeader/{patrolLeaderId}", function (Request $request, Response $response, array $args) {
				$this->patrolService->openPatrol($args['patrolLeaderId']);
				$this->flashMessages->info('Patrola zamítnuta');
				
				return $response->withRedirect($this->router->pathFor('admin-approving'));
			})->setName('openPatrolLeaderConfirmed');
			
			
			$this->post("/approveIst/{istId}", function (Request $request, Response $response, array $args) {
				$istId = $args['istId'];
				$this->istService->approveIst($istId);
				/** @var \kissj\Payment\PaymentService $paymentService */
				$paymentService = $this->paymentService;
				$role = $this->roleService->getRole($this->istService->getIstFromId($istId)->user);
				$payment = $paymentService->createNewPayment($role);
				$paymentService->sendPaymentByMail($payment);
				
				$this->flashMessages->success('Člen IST schválen');
				
				return $response->withRedirect($this->router->pathFor('admin-approving'));
			})->setName('approveIst');
			
			$this->get("/openIst/{istId}", function (Request $request, Response $response, array $args) {
				$ist = $this->istService->getIstFromId($args['istId']);
				return $this->view->render($response, 'admin/openIst.twig', ['ist' => $ist]);
			})->setName('openIst');
			
			$this->post("/openIst/{istId}", function (Request $request, Response $response, array $args) {
				$this->istService->openIst($args['istId']);
				$this->flashMessages->info('Člen IST zamítnut');
				
				return $response->withRedirect($this->router->pathFor('admin-approving'));
			})->setName('openIstConfirmed');
			
		})->add(function (Request $request, Response $response, callable $next) {
			// protected area for Registration Admins
			if ($this->roleService->getRole($request->getAttribute('user'))->name != 'admin') {
				$this->flashMessages->error('Pardon, nejsi na akci vedený jako admin');
				return $response->withRedirect($this->router->pathFor('loginAskEmail'));
			} else {
				$response = $next($request, $response);
				return $response;
			}
		});
		
	})->add(function (Request $request, Response $response, callable $next) {
		// protected area for logged users only
		if (is_null($this->roleService->getRole($request->getAttribute('user')))) {
			$this->flashMessages->warning('Pardon, ale nejsi přihlášený. Přihlaš se prosím');
			return $response->withRedirect($this->router->pathFor('landing'));
		} else {
			$response = $next($request, $response);
			return $response;
		}
	});
	
	$this->any("/admin", function (Request $request, Response $response, array $args) {
		global $adminerSettings;
		$adminerSettings = $this->get('settings')['adminer'];
		require __DIR__."/../admin/custom.php";
	})->setName('administration');

// LANDING PAGE
	
	$this->get("", function (Request $request, Response $response, array $args) {
		return $this->view->render($response, 'landing-page.twig');
	})->setName("landing")->add(function (Request $request, Response $response, callable $next) {
		// protected area for non-logged users only
		if (is_null($this->roleService->getRole($request->getAttribute('user')))) {
			$response = $next($request, $response);
			return $response;
		} else {
			return $response->withRedirect($this->router->pathFor('getDashboard'));
		}
	});
});

$app->get("/", function (Request $request, Response $response, array $args) {
	//return $this->view->render($response, 'system/landing.twig');
	return $response->withRedirect($this->router->pathFor('landing'));
});