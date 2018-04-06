<?php

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
			if ($this->get('settings')['useTestingSite']) {
				$this->flashMessages->success('Úspěch!');
				$this->flashMessages->warning('Pozor!');
				$this->flashMessages->error('Něco se pokazilo!');
				$this->flashMessages->info('Informace.');
			}

			return $this->view->render($response, 'registrationLostSoul.twig');
		})->setName('registrationLostSoul');

		/*
		
		#    #  ####  ###### #####
		#    # #      #      #    #
		#    #  ####  #####  #    #
		#    #      # #      #####
		#    # #    # #      #   #
		 ####   ####  ###### #    #
		
		*/

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
				$this->logger->info('Created new user with email '.$email);
				return $response->withRedirect($this->router->pathFor('signupSuccess'));
			} catch (Exception $e) {
				$this->logger->addError("Error sending registration email to $email with token ".
					$this->userService->getTokenForEmail($email), array ($e));
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
				$user = $this->userService->getUserFromEmail($email);
				/** @var \kissj\User\Role $role */
				$role = $this->roleService->getRole($user);
				$readableRole = $this->roleService->getReadableRoleName($role->name);
				try {
					$this->userService->sendLoginTokenByMail($email, $readableRole);
				} catch (Exception $e) {
					$this->logger->addError("Error sending login email to $email with token ".
						$this->userService->getTokenForEmail($email), array ($e));
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
					/** @var \kissj\Participant\Ist\IstService $istService */
					$istService = $this->istService;
					if ($istService->isIstDetailsValid(
						$params['firstName'] ?? null,
						$params['lastName'] ?? null,
						$params['nickname'] ?? null,
						$params['birthDate'] ?? null,
						$params['gender'] ?? null,
						$params['permanentResidence'] ?? null,
						$params['email'] ?? null,
						$params['legalRepresestative'] ?? null,
						$params['scarf'] ?? null,
						$params['notes'] ?? null)) {

						$istService->editIstInfo(
							$this->istService->getIst($request->getAttribute('user')),
							$params['firstName'] ?? null,
							$params['lastName'] ?? null,
							$params['nickname'] ?? null,
							$params['birthDate'] ?? null,
							$params['gender'] ?? null,
							$params['permanentResidence'] ?? null,
							$params['email'] ?? null,
							$params['legalRepresestative'] ?? null,
							$params['scarf'] ?? null,
							$params['notes'] ?? null);

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
				$istStatistics = $this->get('istService')->getAllIstsStatistics();

				return $this->view->render($response, 'admin/dashboard-admin.twig', ['eventName' => 'Korbo 2018', 'ists' => $istStatistics]);
			})->setName('admin-dashboard');

			// APPROVING

			$this->group("/approving", function () {

				$this->get("", function (Request $request, Response $response, array $args) {
					$closedIsts = $this->istService->getAllClosedIsts();

					return $this->view->render($response, 'admin/approving-admin.twig', ['eventName' => 'Korbo 2018', 'closedIsts' => $closedIsts]);
				})->setName('admin-approving');

				$this->post("/approveIst/{istId}", function (Request $request, Response $response, array $args) {
					/** @var \kissj\Participant\Ist\IstService $istService */
					$istService = $this->istService;
					$ist = $istService->getIstFromId($args['istId']);
					$istService->approveIst($ist);
					$role = $this->roleService->getRole($ist->user);
					$extraScarf = $ist->scarf === 'ano'; // YAGNI in practise
					$payment = $this->paymentService->createNewPayment($role, $extraScarf);
					$istService->sendPaymentByMail($payment, $ist);
					$this->flashMessages->success('Účastník schválen, platba vygenerována a mail odeslán');
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
					$this->flashMessages->info('Účastník zamítnut, email o zamítnutí poslán');
					$this->logger->info('Denied registration for attendee with ID '.$ist->id.' with reason: '.$reason);

					return $response->withRedirect($this->router->pathFor('admin-approving'));
				})->setName('openIstConfirmed');
			});

			// PAYMENTS

			$this->group("/payments", function () {

				$this->get("/manually", function (Request $request, Response $response, array $args) {
					$approvedIsts = $this->istService->getAllApprovedIstsWithPayment();

					$this->view->render($response, 'admin/payments-admin.twig', [
						'eventName' => 'KORBO 2018',
						'approvedIsts' => $approvedIsts,
						'maxElapsedPaymentDays' => $this->get('settings')['paymentSettings']['maxElapsedPaymentDays'],
					]);
				})->setName('admin-payments-manually');

				$this->post("/setPaymentPaid/{payment}", function (Request $request, Response $response, array $args) {
					/** @var \kissj\Payment\PaymentService $paymentService */
					$paymentService = $this->get('paymentService');
					$paymentId = $args['payment'];
					$paymentService->setPaymentPaid($paymentService->getPaymentFromId($paymentId));
					$this->flashMessages->success('Platba je označená jako zaplacená, mail o zaplacení odeslaný');
					$this->logger->info('Payment with ID '.$paymentId.' is set as paid by hand');

					return $response->withRedirect($this->router->pathFor('admin-payments-manually'));
				})->setName('setPaymentPaid');

				$this->group("/auto", function () {

					$this->get("/sinceLastUpdate", function (Request $request, Response $response, array $args) {
						/** @var \kissj\Payment\PaymentService $paymentService */
						$paymentService = $this->get('paymentService');
						// TODO optimalite with new function (getAllIstsPayments() or so)
						$approvedIsts = $this->istService->getAllApprovedIstsWithPayment();
						$paymentService->pairNewPayments($approvedIsts);

						return $response->withRedirect($this->router->pathFor('admin-dashboard'));
					})->setName('admin-payments-auto');

					$this->get("setBreakpoint", function (Request $request, Response $response, array $args) {
						/** @var \kissj\Payment\PaymentService */
						$this->get('paymentService')->setLastDate('2016-01-01');
						$this->get('flashMessages')->success('Poslední break point banky posunut na začátek akce');

						return $response->withRedirect($this->router->pathFor('admin-dashboard'));
					})->setName('admin-payments-setbreakpoint');
				});
			});

			// EXPORTS

			$this->group("/export", function () {

				$this->get("/medical", function (Request $request, Response $response, array $args) {
					$eventName = $this->get('settings')['eventName'];
					$csvRows = $this->exportService->medicalDataToCSV($eventName);
					$this->logger->info('User ID '.$request->getAttribute('user')->id
						.' downloaded current medical data');

					return $this->exportService->createCSVresponse($response, $csvRows, $eventName.'_medical');
				})->setName('admin-export-medical');

				$this->get("/logistic", function (Request $request, Response $response, array $args) {
					$eventName = $this->get('settings')['eventName'];
					$csvRows = $this->exportService->logisticDataPatrolsToCSV($eventName);
					$this->logger->info('User ID '.$request->getAttribute('user')->id
						.' downloaded current logistic data');

					return $this->exportService->createCSVresponse($response, $csvRows, $eventName.'_logistic');
				})->setName('admin-export-logistic');

				$this->get("/paid", function (Request $request, Response $response, array $args) {
					$eventName = $this->get('settings')['eventName'];
					$csvRows = $this->exportService->paidContactDataToCSV($eventName);
					$this->logger->info('User ID '.$request->getAttribute('user')->id
					.' downloaded current contact data about paid participants');

					return $this->exportService->createCSVresponse($response, $csvRows, $eventName.'_paid');
				})->setName('admin-export-paid');

				$this->get("/full", function (Request $request, Response $response, array $args) {
					$eventName = $this->get('settings')['eventName'];
					$csvRows = $this->exportService->allRegistrationDataToCSV($eventName);
					$this->logger->info('User ID '.$request->getAttribute('user')->id
						.' downloaded full current data about participants');

					return $this->exportService->createCSVresponse($response, $csvRows, $eventName.'_full');
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