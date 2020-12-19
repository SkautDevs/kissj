<?php

namespace kissj\User;

use kissj\AbstractController;
use PHPUnit\Framework\MockObject\RuntimeException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends AbstractController {
    protected UserService $userService;
    protected UserRegeneration $userRegeneration;

    public function __construct(
        UserService $userService,
        UserRegeneration $userRegeneration
    ) {
        $this->userService = $userService;
        $this->userRegeneration = $userRegeneration;
    }

    public function landing(Request $request, Response $response, ?User $user): Response {
        if ($user === null) {
            return $this->redirect($request, $response, 'loginAskEmail');
        }

        if ($user->role === User::STATUS_WITHOUT_ROLE) {
            return $this->redirect($request, $response, 'chooseRole', ['eventSlug' => $user->event->slug]);
        }

        return $this->redirect($request, $response, 'getDashboard', ['eventSlug' => $user->event->slug]);
    }

    public function login(Response $response): Response {
        return $this->view->render($response, 'kissj/login.twig', ['event' => null]);
    }

    public function sendLoginEmail(Request $request, Response $response): Response {
        $email = $request->getParsedBody()['email'];
        if (!$this->userService->isEmailExisting($email)) {
            $this->userService->registerUser($email);
        }

        try {
            $this->userService->sendLoginTokenByMail($email, $request);
        } catch (\Exception $e) {
            $this->logger->addError('Error sending login email to '.$email.' with token '.
                $this->userService->getTokenForEmail($email), [$e]);
            $this->flashMessages->error($this->translator->trans('flash.error.mailError'));

            return $this->redirect($request, $response, 'loginAskEmail');
        }

        $this->flashMessages->success($this->translator->trans('flash.success.linkSent'));

        return $this->redirect($request, $response, 'loginAfterLinkSent');
    }

    public function showAfterLinkSent(Response $response): Response {
        return $this->view->render($response, 'kissj/login-link-sent.twig');
    }

    public function tryLoginWithToken(Request $request, Response $response, string $token): Response {
        if ($this->userService->isLoginTokenValid($token)) {
            $loginToken = $this->userService->getLoginTokenFromStringToken($token);
            $user = $loginToken->user;
            $this->userRegeneration->saveUserIdIntoSession($user);
            $this->userService->invalidateAllLoginTokens($user);

            return $this->redirect($request, $response, 'getDashboard', ['eventSlug' => $user->event->slug]);
        }

        $this->flashMessages->warning($this->translator->trans('flash.warning.invalidLogin'));

        return $this->redirect($request, $response, 'loginAskEmail');
    }

    public function logout(Request $request, Response $response): Response {
        $this->userService->logoutUser();
        $this->flashMessages->info($this->translator->trans('flash.info.logout'));

        return $this->redirect($request, $response, 'landing');
    }

    public function chooseRole(User $user, Request $request, Response $response): Response {
        // TODO add preference into get parameter
        return $this->view->render($response, 'kissj/choose-role.twig', ['event' => $user->event,]);
    }

    public function setRole(User $user, Request $request, Response $response): Response {
        $this->userService->setRole($user, $request->getParsedBody()['role']);

        return $this->redirect($request, $response, 'getDashboard', ['eventSlug' => $user->event->slug]);
    }

    public function getDashboard(User $user, Request $request, Response $response): Response {
        $routerEventSlug = ['eventSlug' => $user->event->slug];
        switch ($user->role) {
            case null:
                return $this->redirect($request, $response, 'chooseRole', $routerEventSlug);

            case User::ROLE_IST:
                return $this->redirect($request, $response, 'ist-dashboard', $routerEventSlug);

            case User::ROLE_PATROL_LEADER:
                return $this->redirect($request, $response, 'pl-dashboard', $routerEventSlug);

            case User::ROLE_FREE_PARTICIPANT:
                return $this->redirect($request, $response, 'fp-dashboard', $routerEventSlug);

            case User::ROLE_GUEST:
                return $this->redirect($request, $response, 'guest-dashboard', $routerEventSlug);

            case User::ROLE_ADMIN:
                return $this->redirect($request, $response, 'admin-dashboard', $routerEventSlug);

            default:
                throw new RuntimeException('got unknown role for User id '.$user->id.' with role '.$user->role);
        }
    }

    public function showLoginHelp(Response $response): Response {
        return $this->view->render($response, 'kissj/login-help.twig');
    }
}
