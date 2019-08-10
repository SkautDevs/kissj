<?php

use kissj\Event\EventController;
use kissj\Participant\Admin\AdminController;
use kissj\Participant\Ist\IstController;
use kissj\Participant\Patrol\PatrolController;
use kissj\User\UserController;
use Slim\Http\Request;
use Slim\Http\Response;

$helper['nonLoggedOnly'] = function (Request $request, Response $response, callable $next) {
    // protected area for non-logged users only
    if ($request->getAttribute('user') === null) {
        $response = $next($request, $response);

        return $response;
    }

    if ($event = $request->getAttribute('event')) {
        return $response->withRedirect($this->router->pathFor('getDashboard', ['eventSlug' => $event->slug]));
    }

    return $response->withRedirect($this->router->pathFor('createEvent'));
};

$helper['loggedOnly'] = function (Request $request, Response $response, callable $next) {
    // protected area for logged users only
    if ($request->getAttribute('user') === null) {
        $this->flashMessages->warning('Pardon, ale nejsi přihlášený. Přihlaš se prosím zadáním emailu');
        /** @var \kissj\Event\Event $event */
        if ($event = $request->getAttribute('event')) {
            return $response->withRedirect($this->router->pathFor('landing', ['eventSlug' => $event->slug]));
        }

        return $response->withRedirect($this->router->pathFor('landing'));
    }

    $response = $next($request, $response);

    return $response;
};

$helper['nonChoosedRoleOnly'] = function (Request $request, Response $response, callable $next) {

};

$helper['choosedRoleOnly'] = function (Request $request, Response $response, callable $next) {
    // protected area for users with already choosed role
    // TODO
    if ($request->getAttribute('user') === null) {
        $this->flashMessages->warning('Pardon, ale nejsi přihlášený. Přihlaš se prosím zadáním emailu');
        /** @var \kissj\Event\Event $event */
        if ($event = $request->getAttribute('event')) {
            return $response->withRedirect($this->router->pathFor('landing', ['eventSlug' => $event->slug]));
        }

        return $response->withRedirect($this->router->pathFor('landing'));
    }

    $response = $next($request, $response);

    return $response;
};

$helper['openStatusOnly'] = function (Request $request, Response $response, callable $next) {
    // change data can only users with open registration
    $role = $request->getAttribute('role');
    if ($role->status !== 'open') {
        $this->logger->warning('User '.$request->getAttribute('user')->email.' is trying to change data, even he has role "'.$role->name.'"');
        throw new \RuntimeException('Nemůžeš měnit údaje když nejsi ve stavu zadávání údajů!');
    }

    $response = $next($request, $response);

    return $response;
};

$helper['addEventInfoIntoRequest'] = function (Request $request, Response $response, callable $next) {
    /** @var \kissj\Event\Event $event */
    $event = $this->get('eventService')->getEventFromSlug($request->getAttribute('route')->getArguments()['eventSlug']);
    $request = $request->withAttribute('event', $event);

    $response = $next($request, $response);

    return $response;
};

$app->get('/', function (Request $request, Response $response, array $args) {
    return $response->withRedirect($this->router->pathFor('landing'));
});

$app->group('/v1', function () use ($helper) {
    $this->get('', function (Request $request, Response $response, array $args) {
        return $response->withRedirect($this->router->pathFor('landing'));
    });

    $this->group('/cs', function () use ($helper) {
        $this->get('', function (Request $request, Response $response, array $args) {
            return $response->withRedirect($this->router->pathFor('landing'));
        });

        $this->group('/kissj', function () use ($helper) {
            $this->get('', function (Request $request, Response $response, array $args) {
                return $response->withRedirect($this->router->pathFor('loginAskEmail'));
            })->setName('landing');

            $this->get('/login', function (Request $request, Response $response, array $args) {
                return $this->view->render($response, 'kissj/login.twig');
            })->add($helper['nonLoggedOnly'])->setName('loginAskEmail');

            $this->post('/login', UserController::class.':sendLoginEmail')
                ->add($helper['nonLoggedOnly'])
                ->setName('sendLoginEmail');

            $this->get('/loginAfterLinkSent', function (Request $request, Response $response, array $args) {
                return $this->view->render($response, 'kissj/loginLinkSent.twig');
            })->setName('loginAfterLinkSent');

            $this->get('/login/{token}', UserController::class.':tryLogin')
                ->add($helper['nonLoggedOnly'])
                ->setName('loginWithToken');

            $this->post('/logout', UserController::class.':logout')
                ->add($helper['loggedOnly'])
                ->setName('logout');

            $this->get('/loginHelp', function (Request $request, Response $response, array $args) {
                return $this->view->render($response, 'kissj/loginHelp.twig');
            })->setName('loginHelp');

            $this->get('/createEvent', function (Request $request, Response $response, array $args) {
                return $this->view->render($response, 'kissj/createEvent.twig', ['banks' => $this->banks->getBanks()]);
            })->setName('createEvent')->add($helper['loggedOnly']);

            $this->post('/createEvent', EventController::class.'createEvent')
                ->add($helper['loggedOnly'])
                ->setName('postCreateEvent');
        });

        $this->group('/event/{eventSlug}', function () use ($helper) {
            $this->get('chooseRole', function (Request $request, Response $response, array $args) {
                return $this->view->render($response, 'event/chooseRole.twig');
            })->add($helper['nonChoosedRoleOnly'])->setName('chooseRole');

            $this->post('setRole', EventController::class.':setRole')
                ->add($helper['nonChoosedRoleOnly'])
                ->setName('setRole');

            $this->group('', function () use ($helper) {
                $this->get('/getDashboard', EventController::class.':getDashboard')
                    ->setName('getDashboard');

                $this->group('/patrol', function () use ($helper) {
                    $this->get('/dashboard', PatrolController::class.':showLeaderDashboard')->setName('pl-dashboard');

                    $this->group('', function () {
                        $this->get('/changeDetails', PatrolController::class.':showDetailsLeaderChangeable')
                            ->setName('pl-showDetailsChangeable');

                        $this->post('/changeDetails', PatrolController::class.':changeDetailsLeader')
                            ->setName('pl-changeDetails');

                        $this->get('/closeRegistration', PatrolController::class.':showCloseRegistration')
                            ->setName('pl-showCloseRegistration');

                        $this->post('/closeRegistration', PatrolController::class.':closeRegistration')
                            ->setName('pl-closeRegistration');

                        $this->post('/addParticipant', PatrolController::class.':addParticipant')
                            ->setName('pl-addParticipant');

                        $this->group('/participant/{participantId}', function () {
                            $this->get('/showChangeDetails', PatrolController::class.':showChangeDetailsParticipant')
                                ->setName('p-showChangeDetails');

                            $this->post('/changeDetails', PatrolController::class.':changeDetailsParticipant')
                                ->setName('p-changeDetails');

                            $this->get('/showDelete', PatrolController::class.':showDeleteParticipant')
                                ->setName('p-showDelete');

                            $this->post('/delete', PatrolController::class.':deleteParticipant')
                                ->setName('p-delete');

                        })->add(function (Request $request, Response $response, callable $next) {
                            // participants actions are allowed only for their Patrol Leader
                            $routeParams = $request->getAttribute('routeInfo')[2]; // get route params from request (undocumented feature)
                            if (!$this->patrolService->patrolParticipantBelongsPatrolLeader(
                                $this->patrolService->getPatrolParticipant($routeParams['participantId']),
                                $this->patrolService->getPatrolLeader($request->getAttribute('user')))) {

                                $this->flashMessages->error('Bohužel, nemůžeš provádět akce s účastníky, které neregistruješ ty.');

                                return $response->withRedirect($this->router->pathFor('pl-dashboard'));
                            }

                            $response = $next($request, $response);

                            return $response;
                        });
                    })->add($helper['openStatusOnly']);

                })->add(function (Request $request, Response $response, callable $next) {
                    // protected area for Patrol Leaders
                    if ($this->roleService->getRole($request->getAttribute('user'))->name !== 'patrol-leader') {
                        $this->flashMessages->error('Pardon, nejsi na akci přihlášený jako Patrol Leader');

                        return $response->withRedirect($this->router->pathFor('loginAskEmail'));
                    }

                    $response = $next($request, $response);

                    return $response;
                });

                $this->group('/ist', function () use ($helper) {
                    $this->get('/dashboard', IstController::class.':showDashboard')
                        ->setName('ist-dashboard');

                    $this->group('', function () {
                        $this->get('/showChangeDetails', IstController::class.':showDetailsChangeable')
                            ->setName('ist-showDetailsChangeable');

                        $this->post('/changeDetails', IstController::class.':changeDetails')
                            ->setName('ist-changeDetails');

                        $this->get('/closeRegistration', IstController::class.':showCloseRegistration')
                            ->setName('ist-showCloseRegistration');

                        $this->post('/closeRegistration', IstController::class.':closeRegistration')
                            ->setName('ist-confirmCloseRegistration');

                    })->add($helper['openStatusOnly']);

                })->add(function (Request $request, Response $response, callable $next) {
                    // protected area for IST
                    if ($this->roleService->getRole($request->getAttribute('user'))->name !== 'ist') {
                        $this->flashMessages->error('Pardon, nejsi na akci přihlášený jako IST');

                        return $response->withRedirect($this->router->pathFor('loginAskEmail'));
                    }

                    $response = $next($request, $response);

                    return $response;
                });

                $this->group('/admin', function () {
                    $this->get('/dashboard', AdminController::class.':showDashboard')
                        ->setName('admin-dashboard');

                    $this->group('/approving', function () {
                        $this->get('', AdminController::class.':showApproving')
                            ->setName('admin-approving');

                        // TODO
                        $this->post('/approvePatrolLeader/{patrolLeaderId}',
                            function (Request $request, Response $response, array $args) {
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

                        $this->get('/openPatrolLeader/{patrolLeaderId}',
                            function (Request $request, Response $response, array $args) {
                                $patrolLeader = $this->patrolService->getPatrolLeaderFromId($args['patrolLeaderId']);

                                return $this->view->render($response, 'admin/openPatrolLeader.twig',
                                    ['patrolLeader' => $patrolLeader]);
                            })->setName('openPatrolLeader');

                        $this->post('/openPatrolLeader/{patrolLeaderId}',
                            function (Request $request, Response $response, array $args) {
                                $patrolLeader = $this->patrolService->getPatrolLeaderFromId($args['patrolLeaderId']);
                                $this->patrolService->openPatrol($patrolLeader);
                                $reason = $request->getParsedBodyParam('reason');
                                $this->patrolService->sendDenialMail($patrolLeader, $reason);
                                $this->flashMessages->info('Patrola zamítnuta, email o zamítnutí poslán Patrol Leaderovi');
                                $this->logger->info('Denied registration for Patrol Leader with ID '.$patrolLeader->id.' with reason: '.$reason);

                                return $response->withRedirect($this->router->pathFor('admin-approving'));
                            })->setName('openPatrolLeaderConfirmed');


                        $this->post('/approveIst/{istId}', IstController::class.':approveIst')
                            ->setName('approveIst');

                        $this->get('/openIst/{istId}', IstController::class.'showOpenIst')
                            ->setName('showOpenIst');

                        $this->post('/openIst/{istId}', IstController::class.':openIst')
                            ->setName('openIst');
                    });

                    $this->group('/payments', function () {
                        $this->get('', AdminController::class.':showPayments')
                            ->setName('admin-payments');

                        $this->post('/setPaymentPaid/{payment}', AdminController::class.':setPaymentPaid')
                            ->setName('setPaymentPaid');
                    });

                    $this->group('/export', function () {
                        $this->get('/medical', function (Request $request, Response $response, array $args) {
                            $csvRows = $this->exportService->medicalDataToCSV('cej2018');
                            $this->logger->info('Downloaded current medical data');

                            return $this->exportService->createCSVresponse($response, $csvRows, 'cej2018_medical');
                        })->setName('admin-export-medical');

                        $this->get('/logistic', function (Request $request, Response $response, array $args) {
                            $csvRows = $this->exportService->logisticDataPatrolsToCSV('cej2018');
                            $this->logger->info('Downloaded current logistic data');

                            return $this->exportService->createCSVresponse($response, $csvRows, 'cej2018_logistic');
                        })->setName('admin-export-logistic');

                        $this->get('/full', function (Request $request, Response $response, array $args) {
                            $csvRows = $this->exportService->allRegistrationDataToCSV('cej2018');
                            $this->logger->info('Downloaded full current data about participants');

                            return $this->exportService->createCSVresponse($response, $csvRows, 'cej2018_full');
                        })->setName('admin-export-full');
                    });

                })->add(function (Request $request, Response $response, callable $next) {
                    // protected area for Admins only
                    if ($this->roleService->getRole($request->getAttribute('user'))->name !== 'admin') {
                        $this->flashMessages->error('Pardon, nejsi na akci vedený jako admin');

                        return $response->withRedirect($this->router->pathFor('loginAskEmail'));
                    }

                    $response = $next($request, $response);

                    return $response;
                });
            })->add($helper['loggedOnly'])->add($helper['choosedRoleOnly']);

            $this->any('/administration', function (Request $request, Response $response, array $args) {
                global $adminerSettings;
                $adminerSettings = $this->get('settings')['adminer'];
                require __DIR__.'/../adminer/customAdminerEditor.php';
            })->setName('administration');

        })->add($helper['addEventInfoIntoRequest'])->add($helper['loggedOnly']);
    });
});
