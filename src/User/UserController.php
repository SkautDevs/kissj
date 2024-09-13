<?php

declare(strict_types=1);

namespace kissj\User;

use Exception;
use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Skautis\SkautisService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends AbstractController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected UserService $userService,
        protected UserRegeneration $userRegeneration,
        protected SkautisService $skautisService,
        protected ParticipantService $participantService,
        protected ParticipantRepository $participantRepository,
    ) {
    }

    public function landing(Request $request, Response $response, ?User $user): Response
    {
        if ($user === null) {
            return $this->redirect($request, $response, 'loginAskEmail');
        }

        if ($user->status === UserStatus::WithoutRole) {
            return $this->redirect($request, $response, 'chooseRole');
        }

        return $this->redirect($request, $response, 'getDashboard');
    }

    public function login(Response $response, Event $event): Response
    {
        return $this->view->render(
            $response,
            'kissj/login.twig',
            [
                'skautisLoginUri' => $this->skautisService->getLoginUri($event->slug),
            ],
        );
    }

    public function sendLoginEmail(Request $request, Response $response): Response
    {
        $email = $this->getParameterFromBody($request, 'email', true);
        $event = $this->getEvent($request);
        if (!$this->userRepository->isEmailUserExisting($email, $event)) {
            $this->userService->registerEmailUser($email, $event);
        }

        try {
            $this->userService->sendLoginTokenByMail($email, $request, $event);
        } catch (Exception $e) {
            if ($_ENV['DEBUG'] === 'true') {
                throw new Exception($e->getMessage(), $e->getCode(), $e);
            }
            $this->sentryCollector->collect($e);
            $this->logger->error('Error sending login email to ' . $email . ' with token ' .
                $this->userService->getTokenForEmail($email, $event), [$e]);
            $this->flashMessages->error('flash.error.mailError');

            return $this->redirect($request, $response, 'loginAskEmail');
        }

        $this->flashMessages->success('flash.success.linkSent');

        return $this->redirect($request, $response, 'loginAfterLinkSent');
    }

    public function showAfterLinkSent(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'kissj/login-link-sent.twig');
    }

    public function tryLoginWithToken(Request $request, Response $response, string $token): Response
    {
        if ($this->userService->isLoginTokenValid($token)) {
            $loginToken = $this->userService->getLoginTokenFromStringToken($token);
            $user = $loginToken->user;
            $this->userRegeneration->saveUserIdIntoSession($user);
            $this->userService->invalidateAllLoginTokens($user);

            return $this->redirect($request, $response, 'getDashboard');
        }

        $this->flashMessages->warning('flash.warning.invalidLogin');

        return $this->redirect($request, $response, 'loginAskEmail');
    }

    public function logout(Request $request, Response $response): Response
    {
        $this->userService->logoutUser();
        $this->flashMessages->info('flash.info.logout');

        return $this->redirect($request, $response, 'landing');
    }

    public function chooseRole(User $user, Response $response): Response
    {
        // TODO add preference into get parameter
        return $this->view->render($response, 'kissj/choose-role.twig', ['event' => $user->event,]);
    }

    public function setRole(User $user, Request $request, Response $response): Response
    {
        $participant = $this->userService->createParticipantSetRole(
            $user,
            $this->getParameterFromBody($request, 'role'),
        );

        if ($participant->getUserButNotNull()->loginType === UserLoginType::Skautis) {
            if (!$this->skautisService->isUserLoggedIn()) {
                $this->flashMessages->error('flash.error.skautisUserNotLoggedIn');

                return $this->redirect($request, $response, 'landing');
            }

            $this->skautisService->prefillDataFromSkautis($participant);
        }

        return $this->redirect($request, $response, 'getDashboard');
    }

    public function getDashboard(User $user, Request $request, Response $response): Response
    {
        $routerEventSlug = ['eventSlug' => $user->event->slug];

        return match ($user->role) {
            UserRole::Participant => $this->redirect($request, $response, 'dashboard', $routerEventSlug),
            UserRole::Admin,
            UserRole::IstAdmin,
            UserRole::ContingentAdminCs,
            UserRole::ContingentAdminSk,
            UserRole::ContingentAdminPl,
            UserRole::ContingentAdminHu,
            UserRole::ContingentAdminEu,
            UserRole::ContingentAdminRo,
            UserRole::ContingentAdminGb,
            UserRole::ContingentAdminSw,
            => $this->redirect($request, $response, 'admin-dashboard', $routerEventSlug),
        };
    }

    public function showLoginHelp(Response $response): Response
    {
        return $this->view->render($response, 'kissj/login-help.twig');
    }
}
