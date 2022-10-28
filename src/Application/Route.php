<?php

declare(strict_types=1);

namespace kissj\Application;

use kissj\Event\EventController;
use kissj\Export\ExportController;
use kissj\Middleware\AdminsOnlyMiddleware;
use kissj\Middleware\CheckPatrolLeaderParticipants;
use kissj\Middleware\ChoosedRoleOnlyMiddleware;
use kissj\Middleware\LoggedOnlyMiddleware;
use kissj\Middleware\NonChoosedRoleOnlyMiddleware;
use kissj\Middleware\NonLoggedOnlyMiddleware;
use kissj\Middleware\OpenStatusOnlyMiddleware;
use kissj\Middleware\PatrolLeadersOnlyMiddleware;
use kissj\Participant\Admin\AdminController;
use kissj\Participant\ParticipantController;
use kissj\Participant\Patrol\PatrolController;
use kissj\User\UserController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

class Route
{
    public function addRoutesInto(App $app): App
    {
        $app->redirect($app->getBasePath() ?: '/', $app->getBasePath() . '/v2/kissj', 301);

        $app->group($app->getBasePath() . '/v2', function (RouteCollectorProxy $app) {
            $app->redirect('', $app->getBasePath() . '/v2/kissj', 301);

            $app->group('/kissj', function (RouteCollectorProxy $app) {
                /*
                $app->get('/createEvent', function (Request $request, Response $response) {
                    return $this->get('view')->render($response, 'kissj/createEvent.twig', ['banks' => $this->banks->getBanks()]);
                })->setName('createEvent')->add($helper['loggedOnly']);

                $app->post('/createEvent', EventController::class.'createEvent')
                    ->add($helper['loggedOnly'])
                    ->setName('postCreateEvent');
                */
                $app->get('/events', EventController::class . '::list')
                    ->setName('eventList');
            });

            $app->group('/event/{eventSlug}', function (RouteCollectorProxy $app) {
                $app->get('', UserController::class . '::landing')->setName('landing');

                $app->get('/login', UserController::class . '::login')->add(NonLoggedOnlyMiddleware::class)
                    ->setName('loginAskEmail');

                $app->post('/login', UserController::class . '::sendLoginEmail')
                    ->add(NonLoggedOnlyMiddleware::class)
                    ->setName('sendLoginEmail');

                $app->get('/loginAfterLinkSent', UserController::class . '::showAfterLinkSent')
                    ->setName('loginAfterLinkSent');

                $app->get('/tryLogin/{token}', UserController::class . '::tryLoginWithToken')
                    ->setName('loginWithToken');

                $app->get('/logout', UserController::class . '::logout')
                    ->add(LoggedOnlyMiddleware::class)
                    ->setName('logout');

                $app->get('/loginHelp', UserController::class . '::showLoginHelp')
                    ->setName('loginHelp');

                $app->get('/chooseRole', UserController::class . '::chooseRole')
                    ->add(LoggedOnlyMiddleware::class)
                    ->add(NonChoosedRoleOnlyMiddleware::class)
                    ->setName('chooseRole');

                $app->post('/setRole', UserController::class . '::setRole')
                    ->add(LoggedOnlyMiddleware::class)
                    ->add(NonChoosedRoleOnlyMiddleware::class)
                    ->setName('setRole');

                $app->group('', function (RouteCollectorProxy $app) {
                    $app->get('/getDashboard', UserController::class . '::getDashboard')
                        ->setName('getDashboard');

                    $app->group('/patrol', function (RouteCollectorProxy $app) {
                        $app->get('/dashboard', PatrolController::class . '::showDashboard')
                            ->setName('pl-dashboard');

                        $app->get('/participant/{participantId}/show', PatrolController::class . '::showParticipant')
                            ->setName('p-show');

                        $app->group('', function (RouteCollectorProxy $app) {
                            $app->get('/closeRegistration', PatrolController::class . '::showCloseRegistration')
                                ->setName('pl-showCloseRegistration');

                            $app->post('/closeRegistration', PatrolController::class . '::closeRegistration')
                                ->setName('pl-closeRegistration');

                            $app->post('/addParticipant', PatrolController::class . '::addParticipant')
                                ->setName('pl-addParticipant');

                            $app->group('/participant/{participantId}', function (RouteCollectorProxy $app) {
                                $app->get(
                                    '/showChangeDetails',
                                    PatrolController::class . '::showChangeDetailsPatrolParticipant'
                                )
                                    ->setName('p-showChangeDetails');

                                $app->post(
                                    '/changeDetails',
                                    PatrolController::class . '::changeDetailsPatrolParticipant'
                                )
                                    ->setName('p-changeDetails');

                                $app->get('/showDelete', PatrolController::class . '::showDeleteParticipant')
                                    ->setName('p-showDelete');

                                $app->post('/delete', PatrolController::class . '::deleteParticipant')
                                    ->setName('p-delete');
                            })->add(CheckPatrolLeaderParticipants::class);
                        })->add(OpenStatusOnlyMiddleware::class);
                    })->add(PatrolLeadersOnlyMiddleware::class);

                    // TODO refactor for patrols
                    $app->group('/participant', function (RouteCollectorProxy $app) {
                        $app->get('/dashboard', ParticipantController::class . '::showDashboard')
                            ->setName('dashboard');

                        $app->group('', function (RouteCollectorProxy $app) {
                            $app->get('/showChangeDetails', ParticipantController::class . '::showDetailsChangeable')
                                ->setName('showDetailsChangeable');

                            $app->post('/changeDetails', ParticipantController::class . '::changeDetails')
                                ->setName('changeDetails');

                            $app->get('/closeRegistration', ParticipantController::class . '::showCloseRegistration')
                                ->setName('showCloseRegistration');

                            $app->post('/closeRegistration', ParticipantController::class . '::closeRegistration')
                                ->setName('confirmCloseRegistration');
                        })->add(OpenStatusOnlyMiddleware::class);
                    });
                })->add(LoggedOnlyMiddleware::class)->add(ChoosedRoleOnlyMiddleware::class);

                $app->group('/admin', function (RouteCollectorProxy $app) {
                    $app->get('/dashboard', AdminController::class . '::showDashboard')
                        ->setName('admin-dashboard');

                    $app->post('/changeAdminNote', AdminController::class . '::changeAdminNote')
                        ->setName('admin-change-note');

                    $app->get('/showFile/{filename}', AdminController::class . '::showFile')
                        ->setName('admin-show-file');

                    $app->group('/{participantId}', function (RouteCollectorProxy $app) {
                        $app->get('/showDetails', AdminController::class . '::showParticipantDetails')
                            ->setName('admin-show-participant-details-changeable');

                        $app->post('/changeDetails', AdminController::class . '::changeParticipantDetails')
                            ->setName('admin-change-participant-details');
                    });

                    $app->get('/showStats', AdminController::class . '::showStats')
                        ->setName('admin-show-stats');

                    $app->get('/showOpen', AdminController::class . '::showOpen')
                        ->setName('admin-show-open');

                    $app->group('/approving', function (RouteCollectorProxy $app) {
                        $app->get('', AdminController::class . '::showApproving')
                            ->setName('admin-show-approving');

                        $app->post('/approveParticipant/{participantId}', AdminController::class . '::approveParticipant')
                            ->setName('admin-approve');

                        $app->get('/denyParticipant/{participantId}', AdminController::class . '::showDenyParticipant')
                            ->setName('admin-deny-participant-show');

                        $app->post('/denyParticipant/{participantId}', AdminController::class . '::denyParticipant')
                            ->setName('admin-deny-participant');
                    });

                    $app->group('/payments', function (RouteCollectorProxy $app) {
                        $app->get('', AdminController::class . '::showPayments')
                            ->setName('admin-show-payments');

                        $app->get('/cancelPayment/{paymentId}', AdminController::class . '::showCancelPayment')
                            ->setName('admin-cancel-payment-show');

                        $app->post('/cancelPayment/{paymentId}', AdminController::class . '::cancelPayment')
                            ->setName('admin-cancel-payment');

                        $app->post('/cancelDuePayments', AdminController::class . '::cancelAllDuePayments')
                            ->setName('admin-cancel-due-payments');

                        $app->post('/confirmPayment/{paymentId}', AdminController::class . '::confirmPayment')
                            ->setName('admin-confirm-payment');

                        $app->group('/auto', function (RouteCollectorProxy $app) {
                            $app->get('', AdminController::class . '::showAutoPayments')
                                ->setName('admin-show-auto-payments');

                            $app->post('/setBreakpointFromRoute', AdminController::class . '::setBreakpointFromRoute')
                                ->setName('admin-set-breakpoint-payments');

                            $app->post('/updatePayments', AdminController::class . '::updatePayments')
                                ->setName('admin-update-payments');

                            $app->post(
                                '/setPaymentPaired/{paymentId}',
                                AdminController::class . '::markBankPaymentPaired'
                            )
                                ->setName('admin-set-payment-paired');

                            $app->post(
                                '/setPaymentUnrelated/{paymentId}',
                                AdminController::class . '::markBankPaymentUnrelated'
                            )
                                ->setName('admin-set-payment-unrelated');
                        });

                        $app->get('/showTransferPayment', AdminController::class . '::showTransferPayment')
                            ->setName('admin-show-transfer-payment');

                        $app->post('/transferPayment', AdminController::class . '::transferPayment')
                            ->setName('admin-transfer-payment');

                        $app->post('/generateMorePayments', AdminController::class . '::generateMorePayments')
                            ->setName('admin-generate-more-payments');
                    });

                    $app->group('/export', function (RouteCollectorProxy $app) {
                        $app->get('/health', ExportController::class . '::exportHealthData')
                            ->setName('admin-export-health');

                        $app->get('/paid', ExportController::class . '::exportPaidData')
                            ->setName('admin-export-paid');

                        $app->get('/full', ExportController::class . '::exportFullData')
                            ->setName('admin-export-full');
                    });
                })->add(AdminsOnlyMiddleware::class)->add(LoggedOnlyMiddleware::class);
            });
        });

        return $app;
    }
}
