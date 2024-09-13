<?php

declare(strict_types=1);

namespace kissj\Application;

use DI\Container;
use kissj\Deal\DealController;
use kissj\Entry\EntryController;
use kissj\ParticipantVendor\ParticipantVendorController;
use kissj\Event\EventController;
use kissj\Export\ExportController;
use kissj\Middleware\AddCorsHeaderForAppDomainsMiddleware;
use kissj\Middleware\AdminPaymentsOnlyMiddleware;
use kissj\Middleware\AdminsOnlyMiddleware;
use kissj\Middleware\ApiAuthorizedOnlyMiddleware;
use kissj\Middleware\CheckLeaderParticipants;
use kissj\Middleware\ChoosedRoleOnlyMiddleware;
use kissj\Middleware\LoggedOnlyMiddleware;
use kissj\Middleware\NonChoosedRoleOnlyMiddleware;
use kissj\Middleware\NonLoggedOnlyMiddleware;
use kissj\Middleware\NotGuestMiddleware;
use kissj\Middleware\OpenStatusOnlyMiddleware;
use kissj\Middleware\PaidCancelledStatusOnlyMiddleware;
use kissj\Middleware\PatrolLeadersOnlyMiddleware;
use kissj\Middleware\TroopLeadersOnlyMiddleware;
use kissj\Middleware\TroopParticipantsOnlyMiddleware;
use kissj\Participant\Admin\AdminController;
use kissj\Participant\ParticipantController;
use kissj\Participant\Patrol\PatrolController;
use kissj\Participant\Troop\TroopController;
use kissj\Skautis\SkautisController;
use kissj\User\UserController;
use Slim\App;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;

class Route
{
    /**
     * @param App<Container> $app
     * @return App<Container>
     */
    public function addRoutesInto(App $app): App
    {
        $app->redirect($this->getBasePathSlashPrefixed($app), $app->getBasePath() . '/v2/kissj/events', 301);

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

                $app->post('/skautisRedirect', SkautisController::class . '::redirectFromSkautis')
                    ->setName('skautisRedirect');
            });

            $app->group('/event/{eventSlug}', function (RouteCollectorProxy $app) {
                $app->get('', UserController::class . '::landing')->setName('landing');

                $app->get('/login', UserController::class . '::login')->add(NonLoggedOnlyMiddleware::class)
                    ->setName('loginAskEmail');

                $app->post('/login', UserController::class . '::sendLoginEmail')
                    ->add(NonLoggedOnlyMiddleware::class)
                    ->setName('sendLoginEmail');

                $app->get('/loginAfterLinkSent', UserController::class . '::showAfterLinkSent')
                    ->add(NonLoggedOnlyMiddleware::class)
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
                        $app->get('/participant/{participantId}/show', PatrolController::class . '::showParticipant')
                            ->setName('p-show'); // TODO check if CheckLeaderParticipants is needed here

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
                                    PatrolController::class . '::showChangeDetailsPatrolParticipant',
                                )->setName('p-showChangeDetails');

                                $app->post(
                                    '/changeDetails',
                                    PatrolController::class . '::changeDetailsPatrolParticipant',
                                )->setName('p-changeDetails');

                                $app->get('/showDelete', PatrolController::class . '::showDeleteParticipant')
                                    ->setName('p-showDelete');

                                $app->post('/delete', PatrolController::class . '::deleteParticipant')
                                    ->setName('p-delete');
                            })->add(CheckLeaderParticipants::class);
                        })->add(OpenStatusOnlyMiddleware::class);
                    })->add(PatrolLeadersOnlyMiddleware::class);

                    $app->group('/troop', function (RouteCollectorProxy $app) {
                        $app->post('/tieParticipantToTroopByLeader', TroopController::class . '::tieParticipantToTroopByLeader')
                            ->setName('tie-tp-by-tl')
                            ->add(TroopLeadersOnlyMiddleware::class)
                            ->add(OpenStatusOnlyMiddleware::class);

                        $app->post('/tieParticipantToTroopByParticipant', TroopController::class . '::tieParticipantToTroopByParticipant')
                            ->setName('tie-tp-by-tp')
                            ->add(TroopParticipantsOnlyMiddleware::class);

                        $app->group('/participant/{participantId}', function (RouteCollectorProxy $app) {
                            $app->get('/show', TroopController::class . '::showParticipant')
                                ->setName('tp-show');

                            $app->get('/showUntie', TroopController::class . '::showUntieParticipant')
                                ->setName('tp-showUntie');

                            $app->post('/untie', TroopController::class . '::untieParticipant')
                                ->setName('tp-untie');
                        })->add(CheckLeaderParticipants::class);
                    });

                    // TODO refactor for patrols
                    $app->group('/participant', function (RouteCollectorProxy $app) {
                        $app->get('/dashboard', ParticipantController::class . '::showDashboard')
                            ->setName('dashboard');

                        $app->get('/download/receipt', ParticipantController::class . '::downloadReceipt')
                            ->setName('downloadReceipt')
                            ->add(PaidCancelledStatusOnlyMiddleware::class)
                            ->add(NotGuestMiddleware::class);

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

                    $app->get('/showFile/{filename}', AdminController::class . '::showFile')
                        ->setName('admin-show-file');

                    $app->group('/{participantId}', function (RouteCollectorProxy $app) {
                        $app->get('/mend', AdminController::class . '::mendParticipant')
                            ->setName('admin-mend-participant');

                        $app->post('/uncancel', AdminController::class . '::uncancel')
                            ->setName('admin-uncancel-participant');

                        $app->post('/entry', EntryController::class . '::entryFromAdmin')
                            ->setName('admin-entry-participant');

                        $app->post('/unentry', EntryController::class . '::unentryFromAdmin')
                            ->setName('admin-unentry-participant');

                        $app->post('/setDealAsDone/{dealSlug}', DealController::class . '::setDealAsDone')
                            ->setName('admin-set-deal-as-done');

                        $app->get('/showDetails', AdminController::class . '::showParticipantDetails')
                            ->setName('admin-show-participant-details-changeable');

                        $app->post('/changeDetails', AdminController::class . '::changeParticipantDetails')
                            ->setName('admin-change-participant-details');
                    });

                    $app->group('/changeRole/{participantId}', function (RouteCollectorProxy $app) {
                        $app->get('/show', AdminController::class . '::showRole')
                            ->setName('admin-show-role');

                        $app->post('/change', AdminController::class . '::changeRole')
                            ->setName('admin-change-role');

                        $app->post('/cancel', AdminController::class . '::cancel')
                            ->setName('admin-cancel-participant');
                    });

                    $app->get('/showPaid', AdminController::class . '::showPaid')
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

                            $app->post('/updatePayments', AdminController::class . '::updatePayments')
                                ->setName('admin-update-payments');

                            $app->post(
                                '/setPaymentPaired/{paymentId}',
                                AdminController::class . '::markBankPaymentPaired',
                            )->setName('admin-set-payment-paired');

                            $app->post(
                                '/setPaymentUnrelated/{paymentId}',
                                AdminController::class . '::markBankPaymentUnrelated',
                            )->setName('admin-set-payment-unrelated');
                        });

                        $app->get('/showTransferPayment', AdminController::class . '::showTransferPayment')
                            ->setName('admin-show-transfer-payment');

                        $app->post('/transferPayment', AdminController::class . '::transferPayment')
                            ->setName('admin-transfer-payment');

                        $app->post('/generateMorePayments', AdminController::class . '::generateMorePayments')
                            ->setName('admin-generate-more-payments');
                    })->add(AdminPaymentsOnlyMiddleware::class);

                    $app->group('/troopManagement', function (RouteCollectorProxy $app) {
                        $app->get('', AdminController::class . '::showTroopManagement')
                            ->setName('admin-troop-management');

                        $app->post('/tieTogether', AdminController::class . '::tieTogether')
                            ->setName('admin-troop-tie-together');

                        $app->post('/untie', AdminController::class . '::untie')
                            ->setName('admin-troop-untie');
                    });

                    $app->group('/export', function (RouteCollectorProxy $app) {
                        $app->get('/health', ExportController::class . '::exportHealthData')
                            ->setName('admin-export-health');

                        $app->get('/paid', ExportController::class . '::exportPaidData')
                            ->setName('admin-export-paid');

                        $app->get('/full', ExportController::class . '::exportFullData')
                            ->setName('admin-export-full');

                        $app->get('patrolsRoster', ExportController::class . '::exportPatrolsRoster')
                            ->setName('admin-export-patrols-roster');
                    });

                    $app->post('/import/ist', AdminController::class . '::importIstFromSrs')
                        ->setName('admin-import-ist');
                })->add(AdminsOnlyMiddleware::class)->add(LoggedOnlyMiddleware::class);
            });
        });

        $app->group($app->getBasePath() . '/v3', function (RouteCollectorProxy $app) {
            $app->post('/deal', DealController::class . '::catchDataFromGoogleForm')
                ->add(ApiAuthorizedOnlyMiddleware::class)
                ->setName('deal-catch-data-from-google-form');

            $app->group('/entry', function (RouteCollectorProxy $app) {
                $app->get('/list', EntryController::class . '::list')
                    ->add(ApiAuthorizedOnlyMiddleware::class)
                    ->setName('entry-list');

                $app->post('/code/{entryCode}', EntryController::class . '::entry')
                    ->setName('entry');

                $app->map(['POST', 'OPTIONS'], '/participant/{participantId}', EntryController::class . '::entryParticipantFromWebApp')
                    ->add(ApiAuthorizedOnlyMiddleware::class)
                    ->add(AddCorsHeaderForAppDomainsMiddleware::class)
                    ->setName('entry-participant-from-web-app');

                // TODO change route name here and in entry app
                $app->map(['POST', 'OPTIONS'], '/troop/{participantId}', EntryController::class . '::entryGroupFromWebApp')
                    ->add(ApiAuthorizedOnlyMiddleware::class)
                    ->add(AddCorsHeaderForAppDomainsMiddleware::class)
                    ->setName('entry-troop-from-web-app');
            });

            $app->group('/leave', function (RouteCollectorProxy $app) {
                $app->map(['POST', 'OPTIONS'], '/participant/{participantId}', EntryController::class . '::leaveParticipantFromWebApp')
                    ->add(ApiAuthorizedOnlyMiddleware::class)
                    ->add(AddCorsHeaderForAppDomainsMiddleware::class)
                    ->setName('leave-participant-from-web-app');

                $app->map(['POST', 'OPTIONS'], '/troop/{participantId}', EntryController::class . '::leaveTroopFromWebApp')
                    ->add(ApiAuthorizedOnlyMiddleware::class)
                    ->add(AddCorsHeaderForAppDomainsMiddleware::class)
                    ->setName('leave-troop-from-web-app');
            });

            $app->group('/vendor', function (RouteCollectorProxy $app) {
                $app->map(['POST', 'OPTIONS'], '/bearercheck', function (Response $response) { return $response->withStatus(200); })
                    ->add(ApiAuthorizedOnlyMiddleware::class)
                    ->add(AddCorsHeaderForAppDomainsMiddleware::class)
                    ->setName('entry-participant-from-web-app');

                $app->map(['GET', 'OPTIONS'], '/participant/{tieCode}', ParticipantVendorController::class . '::retrieveParticipantByTieCode')
                    ->add(ApiAuthorizedOnlyMiddleware::class)
                    ->add(AddCorsHeaderForAppDomainsMiddleware::class)
                    ->setName('entry-troop-from-web-app');
            });

            $app->group('/event/{eventSlug}', function (RouteCollectorProxy $app) {
                $app->group('/admin', function (RouteCollectorProxy $app) {
                    $app->group('/{participantId}', function (RouteCollectorProxy $app) {
                        $app->post('/adminNote', AdminController::class . '::changeAdminNote')
                            ->setName('admin-change-note');
                    });
                })->add(AdminsOnlyMiddleware::class)->add(LoggedOnlyMiddleware::class);
            });
        });

        $app->get($app->getBasePath() . '/{eventSlug}', EventController::class . '::redirectEvent')
            ->setName('redirectEvent');

        return $app;
    }

    /**
     * @param App<Container> $app
     */
    private function getBasePathSlashPrefixed(App $app): string
    {
        $basePath = $app->getBasePath();
        if ($basePath === '') {
            return '/';
        }

        return $basePath;
    }
}
