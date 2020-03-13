<?php

namespace kissj\User;

use kissj\AbstractController;
use PHPUnit\Framework\MockObject\RuntimeException;
use Slim\Http\Request;
use Slim\Http\Response;

class UserController extends AbstractController {
    protected $userService;
    protected $userRegeneration;

    public function __construct(
        UserService $userService,
        UserRegeneration $userRegeneration
    ) {
        $this->userService = $userService;
        $this->userRegeneration = $userRegeneration;
    }

    public function landing(Response $response, ?User $user) {
        if ($user === null) {
            return $response->withRedirect($this->router->pathFor('loginAskEmail'));
        }

        if ($user->role === User::STATUS_WITHOUT_ROLE) {
            return $response->withRedirect($this->router->pathFor('chooseRole', ['eventSlug' => $user->event->slug]));
        }

        return $response->withRedirect($this->router->pathFor('getDashboard', ['eventSlug' => $user->event->slug]));
    }

    public function sendLoginEmail(Request $request, Response $response) {
        $email = $request->getParam('email');
        if (!$this->userService->isEmailExisting($email)) {
            $this->userService->registerUser($email);
        }

        try {
            $this->userService->sendLoginTokenByMail($email);
        } catch (\RuntimeException $e) {
            $this->logger->addError("Error sending login email to $email with token ".
                $this->userService->getTokenForEmail($email), array($e));
            $this->flashMessages->error('E-mail sending failed. Please try it in a couple of minutes. ');

            return $response->withRedirect($this->router->pathFor('loginAskEmail'));
        }

        $this->flashMessages->success('E-mail sent! Follow the link in it to log in.');

        return $response->withRedirect($this->router->pathFor('loginAfterLinkSent'));
    }

    public function tryLoginWithToken(Response $response, string $token) {
        if ($this->userService->isLoginTokenValid($token)) {
            $loginToken = $this->userService->getLoginTokenFromStringToken($token);
            $user = $loginToken->user;
            $this->userRegeneration->saveUserIdIntoSession($user);
            $this->userService->invalidateAllLoginTokens($user);

            return $response->withRedirect($this->router->pathFor('getDashboard', ['eventSlug' => $user->event->slug]));
        }

        $this->flashMessages->warning('Log-in button you have used is not valid. Please, enter your e-mail at the registration homepage again to get a new one.');

        return $response->withRedirect($this->router->pathFor('loginAskEmail'));
    }

    public function logout(Response $response) {
        $this->userService->logoutUser();
        $this->flashMessages->info('Logout was successful');

        return $response->withRedirect($this->router->pathFor('landing'));
    }

    public function setRole(User $user, Request $request, Response $response) {
        $this->userService->setRole($user, $request->getParam('role'));

        return $response->withRedirect($this->router->pathFor('getDashboard', ['eventSlug' => $user->event->slug]));
    }

    public function getDashboard(User $user, Response $response) {
        $routerEventSlug = ['eventSlug' => $user->event->slug];
        switch ($user->role) {
            case null:
                return $response->withRedirect($this->router->pathFor('chooseRole', $routerEventSlug));

            case User::ROLE_IST:
                return $response->withRedirect($this->router->pathFor('ist-dashboard', $routerEventSlug));

            case User::ROLE_PATROL_LEADER:
                return $response->withRedirect($this->router->pathFor('pl-dashboard', $routerEventSlug));

            case User::ROLE_FREE_PARTICIPANT:
                return $response->withRedirect($this->router->pathFor('fp-dashboard', $routerEventSlug));

            case User::ROLE_GUEST:
                return $response->withRedirect($this->router->pathFor('guest-dashboard', $routerEventSlug));

            case User::ROLE_ADMIN:
                return $response->withRedirect($this->router->pathFor('admin-dashboard', $routerEventSlug));

            default:
                throw new RuntimeException('got unknown role for User id '.$user->id.' with role '.$user->role);
        }
    }
}
