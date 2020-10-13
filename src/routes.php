<?php

use kissj\Export\ExportController;
use kissj\Middleware\AdminsOnlyMiddleware;
use kissj\Middleware\ChoosedRoleOnlyMiddleware;
use kissj\Middleware\IstsOnlyMiddleware;
use kissj\Middleware\LoggedOnlyMiddleware;
use kissj\Middleware\NonChoosedRoleOnlyMiddleware;
use kissj\Middleware\NonLoggedOnlyMiddleware;
use kissj\Middleware\OpenStatusOnlyMiddleware;
use kissj\Participant\Admin\AdminController;
use kissj\Participant\FreeParticipant\FreeParticipantController;
use kissj\Participant\Guest\GuestController;
use kissj\Participant\Ist\IstController;
use kissj\Participant\Patrol\PatrolController;
use kissj\User\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteCollectorProxy;

$app->redirect($app->getBasePath() ?: '/', $app->getBasePath().'/v2/kissj', 301);

$app->group($app->getBasePath().'/v2', function (RouteCollectorProxy $app) {
    $app->redirect('', $app->getBasePath().'/v2/kissj', 301);

    $app->group('/kissj', function (RouteCollectorProxy $app) {
        $app->get('', UserController::class.'::landing')->setName('landing');

        $app->get('/login', UserController::class.'::login')->add(NonLoggedOnlyMiddleware::class)
            ->setName('loginAskEmail');

        $app->post('/login', UserController::class.'::sendLoginEmail')
            ->add(NonLoggedOnlyMiddleware::class)
            ->setName('sendLoginEmail');

        $app->get('/loginAfterLinkSent', UserController::class.'::showAfterLinkSent')
            ->setName('loginAfterLinkSent');

        $app->get('/tryLogin/{token}', UserController::class.'::tryLoginWithToken')
            ->setName('loginWithToken');

        $app->get('/logout', UserController::class.'::logout')
            ->add(LoggedOnlyMiddleware::class)
            ->setName('logout');

        $app->get('/loginHelp', UserController::class.'::showLoginHelp')
            ->setName('loginHelp');
        /*
        $app->get('/createEvent', function (Request $request, Response $response) {
            return $this->get('view')->render($response, 'kissj/createEvent.twig', ['banks' => $this->banks->getBanks()]);
        })->setName('createEvent')->add($helper['loggedOnly']);

        $app->post('/createEvent', EventController::class.'createEvent')
            ->add($helper['loggedOnly'])
            ->setName('postCreateEvent');
        */

        $app->any('/administration', function (Request $request, Response $response) {
            require __DIR__.'/../adminer/customAdminerEditor.php';
            die();
        })->setName('administration');
    });

    $app->group('/event/{eventSlug}', function (RouteCollectorProxy $app) {
        $app->get('/chooseRole', UserController::class.'::chooseRole')
            ->add(LoggedOnlyMiddleware::class)
            ->add(NonChoosedRoleOnlyMiddleware::class)
            ->setName('chooseRole');

        $app->post('/setRole', UserController::class.'::setRole')
            ->add(LoggedOnlyMiddleware::class)
            ->add(NonChoosedRoleOnlyMiddleware::class)
            ->setName('setRole');

        $app->group('', function (RouteCollectorProxy $app) {
            $app->get('/getDashboard', UserController::class.'::getDashboard')
                ->setName('getDashboard');

            $app->group('/patrol', function (RouteCollectorProxy $app) {
                $app->get('/dashboard', PatrolController::class.'::showDashboard')
                    ->setName('pl-dashboard');

                $app->get('/participant/{participantId}/show', PatrolController::class.'::showParticipant')
                    ->setName('p-show');

                $app->group('', function (RouteCollectorProxy $app) {
                    $app->get('/changeDetails', PatrolController::class.'::showDetailsChangeableLeader')
                        ->setName('pl-showDetailsChangeable');

                    $app->post('/changeDetails', PatrolController::class.'::changeDetailsLeader')
                        ->setName('pl-changeDetails');

                    $app->get('/closeRegistration', PatrolController::class.'::showCloseRegistration')
                        ->setName('pl-showCloseRegistration');

                    $app->post('/closeRegistration', PatrolController::class.'::closeRegistration')
                        ->setName('pl-closeRegistration');

                    $app->post('/addParticipant', PatrolController::class.'::addParticipant')
                        ->setName('pl-addParticipant');

                    $app->group('/participant/{participantId}', function (RouteCollectorProxy $app) {
                        $app->get('/showChangeDetails',
                            PatrolController::class.'::showChangeDetailsPatrolParticipant')
                            ->setName('p-showChangeDetails');

                        $app->post('/changeDetails', PatrolController::class.'::changeDetailsPatrolParticipant')
                            ->setName('p-changeDetails');

                        $app->get('/showDelete', PatrolController::class.'::showDeleteParticipant')
                            ->setName('p-showDelete');

                        $app->post('/delete', PatrolController::class.'::deleteParticipant')
                            ->setName('p-delete');

                    })->add(\kissj\Middleware\CheckPatrolLeaderParticipants::class);
                })->add(OpenStatusOnlyMiddleware::class);
            })->add(\kissj\Middleware\PatrolLeadersOnlyMiddleware::class);
            
            $app->group('/ist', function (RouteCollectorProxy $app) {
                $app->get('/dashboard', IstController::class.'::showDashboard')
                    ->setName('ist-dashboard');

                $app->group('', function (RouteCollectorProxy $app) {
                    $app->get('/showChangeDetails', IstController::class.'::showDetailsChangeable')
                        ->setName('ist-showDetailsChangeable');

                    $app->post('/changeDetails', IstController::class.'::changeDetails')
                        ->setName('ist-changeDetails');

                    $app->get('/closeRegistration', IstController::class.'::showCloseRegistration')
                        ->setName('ist-showCloseRegistration');

                    $app->post('/closeRegistration', IstController::class.'::closeRegistration')
                        ->setName('ist-confirmCloseRegistration');

                })->add(OpenStatusOnlyMiddleware::class);

            })->add(IstsOnlyMiddleware::class);
            /*
                        $app->group('/freeParticipant', function (RouteCollectorProxy $app) {
                            $app->get('/dashboard', FreeParticipantController::class.'::showDashboard')
                                ->setName('fp-dashboard');
            
                            $app->group('', function (RouteCollectorProxy $app) {
                                $app->get('/showChangeDetails', FreeParticipantController::class.'::showDetailsChangeable')
                                    ->setName('fp-showDetailsChangeable');
            
                                $app->post('/changeDetails', FreeParticipantController::class.'::changeDetails')
                                    ->setName('fp-changeDetails');
            
                                $app->get('/closeRegistration', FreeParticipantController::class.'::showCloseRegistration')
                                    ->setName('fp-showCloseRegistration');
            
                                $app->post('/closeRegistration', FreeParticipantController::class.'::closeRegistration')
                                    ->setName('fp-confirmCloseRegistration');
            
                            })->add(OpenStatusOnlyMiddleware::class);
            
                        })->add(function (Request $request, RequestHandler $handler) use ($app) {
                            // protected area for Free Participants
                            if ($request->getAttribute('user')->role !== User::ROLE_FREE_PARTICIPANT) {
                                $this->get('flashMessages')->error('Pardon, you are not registred as Free Participant');
            
                                $url = $app->getRouteCollector()->getRouteParser()->urlFor('landing');
                                $response = new \Slim\Psr7\Response();
            
                                return $response->withHeader('Location', $url)->withStatus(302);
                            }
            
                            return $handler->handle($request);
                        });
            */ /*
            $app->group('/guest', function (RouteCollectorProxy $app) {
                $app->get('/dashboard', GuestController::class.'::showDashboard')
                    ->setName('guest-dashboard');

                $app->group('', function (RouteCollectorProxy $app) {
                    $app->get('/showChangeDetails', GuestController::class.'::showDetailsChangeable')
                        ->setName('guest-showDetailsChangeable');

                    $app->post('/changeDetails', GuestController::class.'::changeDetails')
                        ->setName('guest-changeDetails');

                    $app->get('/closeRegistration', GuestController::class.'::showCloseRegistration')
                        ->setName('guest-showCloseRegistration');

                    $app->post('/closeRegistration', GuestController::class.'::closeRegistration')
                        ->setName('guest-confirmCloseRegistration');

                })->add(OpenStatusOnlyMiddleware::class);

            })->add(function (Request $request, RequestHandler $handler) use ($app) {
                // protected area for guests
                if ($request->getAttribute('user')->role !== User::ROLE_GUEST) {
                    $this->get('flashMessages')->error('Pardon, you are not registred as guest');

                    $url = $app->getRouteCollector()->getRouteParser()->urlFor('landing');
                    $response = new \Slim\Psr7\Response();

                    return $response->withHeader('Location', $url)->withStatus(302);
                }

                return $handler->handle($request);
            });
*/
            $app->redirect('', $app->getRouteCollector()->getRouteParser()->urlFor('landing'));
        })->add(LoggedOnlyMiddleware::class)->add(ChoosedRoleOnlyMiddleware::class);

        $app->group('/admin', function (RouteCollectorProxy $app) {
            $app->get('/dashboard', AdminController::class.'::showDashboard')
                ->setName('admin-dashboard');

            $app->get('/showFile/{filename}', AdminController::class.'::showFile')
                ->setName('admin-show-file');

            $app->group('/approving', function (RouteCollectorProxy $app) {
                $app->get('', AdminController::class.'::showApproving')
                    ->setName('admin-show-approving');

                $app->get('/openPatrolLeader/{patrolLeaderId}', PatrolController::class.'::showOpenPatrol')
                    ->setName('admin-open-pl-show');

                $app->post('/openPatrolLeader/{patrolLeaderId}', PatrolController::class.'::openPatrol')
                    ->setName('admin-open-pl');

                $app->post('/approvePatrolLeader/{patrolLeaderId}', PatrolController::class.'::approvePatrol')
                    ->setName('admin-approve-pl');

                $app->get('/openIst/{istId}', IstController::class.'::showOpenIst')
                    ->setName('admin-open-ist-show');

                $app->post('/openIst/{istId}', IstController::class.'::openIst')
                    ->setName('admin-open-ist');

                $app->post('/approveIst/{istId}', IstController::class.'::approveIst')
                    ->setName('admin-approve-ist');

                $app->get('/openFreeParticipant/{fpId}', FreeParticipantController::class.'::showOpenFreeParticipant')
                    ->setName('admin-open-fp-show');

                $app->post('/openFreeParticipant/{fpId}', FreeParticipantController::class.'::openFreeParticipant')
                    ->setName('admin-open-fp');

                $app->post('/welcomeFreeParticipant/{fpId}', FreeParticipantController::class.'::mailWelcomeFreeParticipant')
                    ->setName('admin-fp-send-welcome');

                $app->post('/approveFreeParticipant/{fpId}', FreeParticipantController::class.'::approveFreeParticipant')
                    ->setName('admin-approve-fp');

                $app->get('/openGuest/{guestId}', GuestController::class.'::showOpenGuest')
                    ->setName('admin-open-guest-show');

                $app->post('/openGuest/{guestId}', GuestController::class.'::openGuest')
                    ->setName('admin-open-guest');

                $app->post('/approveGuest/{guestId}', GuestController::class.'::approveGuest')
                    ->setName('admin-approve-guest');
            });

            $app->group('/payments', function (RouteCollectorProxy $app) {
                $app->get('', AdminController::class.'::showPayments')
                    ->setName('admin-show-payments');

                $app->get('/cancelPayment/{paymentId}', AdminController::class.'::showCancelPayment')
                    ->setName('admin-cancel-payment-show');

                $app->post('/cancelPayment/{paymentId}', AdminController::class.'::cancelPayment')
                    ->setName('admin-cancel-payment');

                $app->post('/cancelDuePayments', AdminController::class.'::cancelAllDuePayments')
                    ->setName('admin-cancel-due-payments');

                $app->post('/confirmPayment/{paymentId}', AdminController::class.'::confirmPayment')
                    ->setName('admin-confirm-payment');

                $app->group('/auto', function (RouteCollectorProxy $app) {
                    $app->get('', AdminController::class.'::showAutoPayments')
                        ->setName('admin-show-auto-payments');

                    $app->post('/setBreakpointFromRoute', AdminController::class.'::setBreakpointFromRoute')
                        ->setName('admin-set-breakpoint-payments');

                    $app->post('/updatePayments', AdminController::class.'::updatePayments')
                        ->setName('admin-update-payments');

                    $app->post('/setPaymentPaired/{paymentId}', AdminController::class.'::markBankPaymentPaired')
                        ->setName('admin-set-payment-paired');

                    $app->post('/setPaymentUnrelated/{paymentId}', AdminController::class.'::markBankPaymentUnrelated')
                        ->setName('admin-set-payment-unrelated');
                });

                $app->get('/showTransferPayment', AdminController::class.'::showTransferPayment')
                    ->setName('admin-show-transfer-payment');

                $app->post('/transferPayment', AdminController::class.'::transferPayment')
                    ->setName('admin-transfer-payment');
            });

            $app->group('/export', function (RouteCollectorProxy $app) {
                $app->get('/health', ExportController::class.'::exportHealthData')
                    ->setName('admin-export-health');

                $app->get('/paid', ExportController::class.'::exportPaidData')
                    ->setName('admin-export-paid');

                $app->get('/full', ExportController::class.'::exportFullData')
                    ->setName('admin-export-full');
            });

        })->add(AdminsOnlyMiddleware::class)->add(LoggedOnlyMiddleware::class);
    });
});
