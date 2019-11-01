<?php

use kissj\Participant\Admin\AdminController;
use kissj\Participant\Guest\GuestController;
use kissj\Participant\Ist\IstController;
use kissj\Participant\Patrol\PatrolController;
use kissj\User\User;
use kissj\User\UserController;
use Slim\Http\Request;
use Slim\Http\Response;

$helper['nonLoggedOnly'] = function (Request $request, Response $response, callable $next) {
    // protected area for non-logged users only
    if ($request->getAttribute('user') !== null) {
        $this->get('flashMessages')->warning('Pardon, but your are logged - you need to sign off first');

        return $response->withRedirect($this->get('router')->pathFor('landing'));
    }
    $response = $next($request, $response);

    return $response;
};

$helper['loggedOnly'] = function (Request $request, Response $response, callable $next) {
    // protected area for logged users only
    if ($request->getAttribute('user') === null) {
        $this->get('flashMessages')->warning('Pardon, but you are not logged. Please log yourself via entering your email');

        return $response->withRedirect($this->get('router')->pathFor('loginAskEmail'));
    }
    $response = $next($request, $response);

    return $response;
};

$helper['nonChoosedRoleOnly'] = function (Request $request, Response $response, callable $next) {
    // protected area for users with already choosed role
    /** @var User $user */
    $user = $request->getAttribute('user');

    if ($user->status !== User::STATUS_WITHOUT_ROLE) {
        $this->get('flashMessages')->warning('Pardon, you already choosed your role for this event');

        return $response->withRedirect($this->get('router')->pathFor('landing'));
    }

    $response = $next($request, $response);

    return $response;
};

$helper['choosedRoleOnly'] = function (Request $request, Response $response, callable $next) {
    // protected area for users with already choosed role
    /** @var User $user */
    $user = $request->getAttribute('user');

    if ($user->status === User::STATUS_WITHOUT_ROLE) {
        $this->get('flashMessages')->info('At first you need to choose your role on event');

        return $response->withRedirect($this->get('router')->pathFor('landing'));
    }
    $response = $next($request, $response);

    return $response;
};

$helper['openStatusOnly'] = function (Request $request, Response $response, callable $next) {
    // change data can only users with open registration
    /** @var User $user */
    $user = $request->getAttribute('user');
    if ($user->status !== User::STATUS_OPEN) {
        $this->get('logger')->warning('User '.$user->email.' is trying to change data, even he has role "'.$user->status.'"');
        throw new \RuntimeException('You cannot change your data when you are not in editing status');
    }
    $response = $next($request, $response);

    return $response;
};

$app->get('/', function (Request $request, Response $response) {
    return $response->withRedirect($this->get('router')->pathFor('landing'));
});

$app->group('/v1', function () use ($helper) {
    $this->get('', function (Request $request, Response $response) {
        return $response->withRedirect($this->get('router')->pathFor('landing'));
    });

    $this->group('/en', function () use ($helper) {
        $this->get('', function (Request $request, Response $response) {
            return $response->withRedirect($this->get('router')->pathFor('landing'));
        });

        $this->group('/kissj', function () use ($helper) {
            $this->get('', UserController::class.'::landing')->setName('landing');

            $this->get('/login', function (Request $request, Response $response) {
                return $this->get('view')->render(
                    $response, 'kissj/login.twig', ['event' => $request->getAttribute('user')->event]
                );
            })->add($helper['nonLoggedOnly'])
                ->setName('loginAskEmail');

            $this->post('/login', UserController::class.'::sendLoginEmail')
                ->add($helper['nonLoggedOnly'])
                ->setName('sendLoginEmail');

            $this->get('/loginAfterLinkSent', function (Request $request, Response $response) {
                return $this->get('view')->render($response, 'kissj/login-link-sent.twig');
            })->setName('loginAfterLinkSent');

            $this->get('/tryLogin/{token}', UserController::class.'::tryLoginWithToken')
                ->setName('loginWithToken');

            $this->get('/logout', UserController::class.'::logout')
                ->add($helper['loggedOnly'])
                ->setName('logout');

            $this->get('/loginHelp', function (Request $request, Response $response) {
                return $this->get('view')->render($response, 'kissj/login-help.twig');
            })->setName('loginHelp');
            /*
            $this->get('/createEvent', function (Request $request, Response $response) {
                return $this->get('view')->render($response, 'kissj/createEvent.twig', ['banks' => $this->banks->getBanks()]);
            })->setName('createEvent')->add($helper['loggedOnly']);

            $this->post('/createEvent', EventController::class.'createEvent')
                ->add($helper['loggedOnly'])
                ->setName('postCreateEvent');
            */
        });

        $this->group('/event/{eventSlug}', function () use ($helper) {
            $this->get('/chooseRole', function (Request $request, Response $response) {
                return $this->get('view')->render($response, 'kissj/choose-role.twig', [
                    'event' => $request->getAttribute('user')->event,
                ]);
            })->add($helper['loggedOnly'])
                ->add($helper['nonChoosedRoleOnly'])
                ->setName('chooseRole');

            $this->post('/setRole', UserController::class.'::setRole')
                ->add($helper['loggedOnly'])
                ->add($helper['nonChoosedRoleOnly'])
                ->setName('setRole');

            $this->group('', function () use ($helper) {
                $this->get('/getDashboard', UserController::class.'::getDashboard')
                    ->setName('getDashboard');

                $this->group('/patrol', function () use ($helper) {
                    $this->get('/dashboard', PatrolController::class.'::showDashboard')
                        ->setName('pl-dashboard');

                    $this->get('/participant/{participantId}/show', PatrolController::class.'::showParticipant')
                        ->setName('p-show');

                    $this->group('', function () use ($helper) {
                        $this->get('/changeDetails', PatrolController::class.'::showDetailsChangeableLeader')
                            ->setName('pl-showDetailsChangeable');

                        $this->post('/changeDetails', PatrolController::class.'::changeDetailsLeader')
                            ->setName('pl-changeDetails');

                        $this->get('/closeRegistration', PatrolController::class.'::showCloseRegistration')
                            ->setName('pl-showCloseRegistration');

                        $this->post('/closeRegistration', PatrolController::class.'::closeRegistration')
                            ->setName('pl-closeRegistration');

                        $this->post('/addParticipant', PatrolController::class.'::addParticipant')
                            ->setName('pl-addParticipant');

                        $this->group('/participant/{participantId}', function () {
                            $this->get('/showChangeDetails',
                                PatrolController::class.'::showChangeDetailsPatrolParticipant')
                                ->setName('p-showChangeDetails');

                            $this->post('/changeDetails', PatrolController::class.'::changeDetailsPatrolParticipant')
                                ->setName('p-changeDetails');

                            $this->get('/showDelete', PatrolController::class.'::showDeleteParticipant')
                                ->setName('p-showDelete');

                            $this->post('/delete', PatrolController::class.'::deleteParticipant')
                                ->setName('p-delete');

                        })->add(function (Request $request, Response $response, callable $next) {
                            // participants actions are allowed only for their Patrol Leader
                            $routeParams = $request->getAttribute('routeInfo')[2]; // get route params from request (undocumented feature)
                            /** @var \kissj\Participant\Patrol\PatrolService $patrolService */
                            $patrolService = $this->get(\kissj\Participant\Patrol\PatrolService::class);
                            if (!$patrolService->patrolParticipantBelongsPatrolLeader(
                                $patrolService->getPatrolParticipant($routeParams['participantId']),
                                $patrolService->getPatrolLeader($request->getAttribute('user')))) {

                                $this->get('flashMessages')->error('Pardon, but you cannot edit or view participants outside your patrol.');

                                return $response->withRedirect($this->get('router')->pathFor('pl-dashboard'));
                            }

                            $response = $next($request, $response);

                            return $response;
                        });
                    })->add($helper['openStatusOnly']);
                })->add(function (Request $request, Response $response, callable $next) {
                    // protected area for Patrol Leaders
                    if ($request->getAttribute('user')->role !== User::ROLE_PATROL_LEADER) {
                        $this->get('flashMessages')->error('Pardon, nejsi na akci přihlášený jako Patrol Leader');

                        return $response->withRedirect($this->get('router')->pathFor('loginAskEmail'));
                    }

                    $response = $next($request, $response);

                    return $response;
                });

                $this->group('/ist', function () use ($helper) {
                    $this->get('/dashboard', IstController::class.'::showDashboard')
                        ->setName('ist-dashboard');

                    $this->group('', function () {
                        $this->get('/showChangeDetails', IstController::class.'::showDetailsChangeable')
                            ->setName('ist-showDetailsChangeable');

                        $this->post('/changeDetails', IstController::class.'::changeDetails')
                            ->setName('ist-changeDetails');

                        $this->get('/closeRegistration', IstController::class.'::showCloseRegistration')
                            ->setName('ist-showCloseRegistration');

                        $this->post('/closeRegistration', IstController::class.'::closeRegistration')
                            ->setName('ist-confirmCloseRegistration');

                    })->add($helper['openStatusOnly']);

                })->add(function (Request $request, Response $response, callable $next) {
                    // protected area for IST
                    if ($request->getAttribute('user')->role !== User::ROLE_IST) {
                        $this->get('flashMessages')->error('Pardon, you are not registred as IST');

                        return $response->withRedirect($this->get('router')->pathFor('landing'));
                    }

                    $response = $next($request, $response);

                    return $response;
                });

                $this->group('/guest', function () use ($helper) {
                    $this->get('/dashboard', GuestController::class.'::showDashboard')
                        ->setName('guest-dashboard');

                    $this->group('', function () {
                        $this->get('/showChangeDetails', GuestController::class.'::showDetailsChangeable')
                            ->setName('guest-showDetailsChangeable');

                        $this->post('/changeDetails', GuestController::class.'::changeDetails')
                            ->setName('guest-changeDetails');

                        $this->get('/closeRegistration', GuestController::class.'::showCloseRegistration')
                            ->setName('guest-showCloseRegistration');

                        $this->post('/closeRegistration', GuestController::class.'::closeRegistration')
                            ->setName('guest-confirmCloseRegistration');

                    })->add($helper['openStatusOnly']);

                })->add(function (Request $request, Response $response, callable $next) {
                    // protected area for guests
                    if ($request->getAttribute('user')->role !== User::ROLE_GUEST) {
                        $this->get('flashMessages')->error('Pardon, you are not registred as guest');

                        return $response->withRedirect($this->get('router')->pathFor('landing'));
                    }

                    $response = $next($request, $response);

                    return $response;
                });

                $this->group('/admin', function () {
                    $this->get('/dashboard', AdminController::class.'::showDashboard')
                        ->setName('admin-dashboard');

                    $this->group('/approving', function () {
                        $this->get('', AdminController::class.'::showApproving')
                            ->setName('admin-approving');

                        // TODO
                        $this->post('/approvePatrolLeader/{patrolLeaderId}',
                            function (Request $request, Response $response, int $patrolLeaderId) {
                                /** @var \kissj\Participant\Patrol\PatrolService $patrolService */
                                $patrolService = $this->patrolService;
                                $patrolLeader = $patrolService->getPatrolLeaderFromId($patrolLeaderId);
                                $patrolService->approvePatrol($patrolLeader);
                                $payment = $this->paymentService->createNewPayment($patrolLeader->user->role);
                                $patrolService->sendPaymentByMail($payment, $patrolLeader);
                                $this->get('flashMessages')->success('Patrola schválena, platba vygenerována a mail odeslán');
                                $this->get('logger')->info('Approved registration for Patrol Leader with ID '.$patrolLeader->id);

                                return $response->withRedirect($this->get('router')->pathFor('admin-approving'));
                            })->setName('approvePatrolLeader');

                        $this->get('/openPatrolLeader/{patrolLeaderId}',
                            function (Request $request, Response $response, int $patrolLeaderId) {
                                $patrolLeader = $this->patrolService->getPatrolLeaderFromId($patrolLeaderId);

                                return $this->get('view')->render($response, 'admin/openPatrolLeader.twig',
                                    ['patrolLeader' => $patrolLeader]);
                            })->setName('openPatrolLeader');

                        $this->post('/openPatrolLeader/{patrolLeaderId}',
                            function (Request $request, Response $response, int $patrolLeaderId) {
                                $patrolLeader = $this->patrolService->getPatrolLeaderFromId($patrolLeaderId);
                                $this->patrolService->openPatrol($patrolLeader);
                                $reason = $request->getParsedBodyParam('reason');
                                $this->patrolService->sendDenialMail($patrolLeader, $reason);
                                $this->get('flashMessages')->info('Patrola zamítnuta, email o zamítnutí poslán Patrol Leaderovi');
                                $this->get('logger')->info('Denied registration for Patrol Leader with ID '.$patrolLeader->id.' with reason: '.$reason);

                                return $response->withRedirect($this->get('router')->pathFor('admin-approving'));
                            })->setName('openPatrolLeaderConfirmed');


                        $this->post('/approveIst/{istId}', IstController::class.'::approveIst')
                            ->setName('approveIst');

                        $this->get('/openIst/{istId}', IstController::class.'showOpenIst')
                            ->setName('showOpenIst');

                        $this->post('/openIst/{istId}', IstController::class.'::openIst')
                            ->setName('openIst');
                    });

                    $this->group('/payments', function () {
                        $this->get('', AdminController::class.'::showPayments')
                            ->setName('admin-payments');

                        $this->post('/setPaymentPaid/{payment}', AdminController::class.'::setPaymentPaid')
                            ->setName('setPaymentPaid');
                    });

                    $this->group('/export', function () {
                        $this->get('/medical', function (Request $request, Response $response) {
                            $csvRows = $this->exportService->medicalDataToCSV('cej2018');
                            $this->get('logger')->info('Downloaded current medical data');

                            return $this->exportService->createCSVresponse($response, $csvRows, 'cej2018_medical');
                        })->setName('admin-export-medical');

                        $this->get('/logistic', function (Request $request, Response $response) {
                            $csvRows = $this->exportService->logisticDataPatrolsToCSV('cej2018');
                            $this->get('logger')->info('Downloaded current logistic data');

                            return $this->exportService->createCSVresponse($response, $csvRows, 'cej2018_logistic');
                        })->setName('admin-export-logistic');

                        $this->get('/full', function (Request $request, Response $response) {
                            $csvRows = $this->exportService->allRegistrationDataToCSV('cej2018');
                            $this->get('logger')->info('Downloaded full current data about participants');

                            return $this->exportService->createCSVresponse($response, $csvRows, 'cej2018_full');
                        })->setName('admin-export-full');
                    });

                })->add(function (Request $request, Response $response, callable $next) {
                    // protected area for Admins only
                    if ($request->getAttribute('user')->role !== User::ROLE_ADMIN) {
                        $this->get('flashMessages')->error('Pardon, nejsi na akci vedený jako admin');

                        return $response->withRedirect($this->get('router')->pathFor('loginAskEmail'));
                    }

                    $response = $next($request, $response);

                    return $response;
                });

                $this->get('', function (Request $request, Response $response) {
                    return $response->withRedirect($this->get('router')->pathFor('landing'));
                });
            })->add($helper['loggedOnly'])->add($helper['choosedRoleOnly']);

            $this->any('/administration', function (Request $request, Response $response) {
                global $adminerSettings;
                $adminerSettings = $this->get('settings')['adminer'];
                require __DIR__.'/../adminer/customAdminerEditor.php';
            })->setName('administration');
        });
    });
});
