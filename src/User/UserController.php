<?php

namespace kissj\User;

use kissj\AbstractController;
use kissj\Participant\ParticipantService;
use PHPUnit\Framework\MockObject\RuntimeException;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class UserController extends AbstractController {
    /** @var UserService */
    protected $userService;
    /** @var UserRegeneration */
    protected $userRegeneration;
    /** @var ParticipantService */
    protected $participantService;

    public function __construct(ContainerInterface $c) {
        $this->userService = $c->get('userService');
        $this->userRegeneration = $c->get('userRegeneration');
        $this->participantService = $c->get('participantService');
        parent::__construct($c);
    }

    public function landing(Request $request, Response $response, array $args) {
        /** @var User $user */
        $user = $request->getAttribute('user');

        if ($user === null) {
            return $response->withRedirect($this->router->pathFor('loginAskEmail'));
        }

        if ($this->participantService->getUserRole($user) === null) {
            return $response->withRedirect($this->router->pathFor('chooseRole', ['eventSlug' => $user->event->slug]));
        }

        return $response->withRedirect($this->router->pathFor('getDashboard', ['eventSlug' => $user->event->slug]));
    }

    public function sendLoginEmail(Request $request, Response $response, array $args) {
        $email = $request->getParam('email');
        if (!$this->userService->isEmailExisting($email)) {
            $this->userService->registerUser($email);
        }

        try {
            $this->userService->sendLoginTokenByMail($email);
        } catch (\RuntimeException $e) {
            $this->logger->addError("Error sending login email to $email with token ".
                $this->userService->getTokenForEmail($email), array ($e));
            $this->flashMessages->error('Nezdařilo se odeslat přihlašovací email. Zkus to prosím za chvíli znovu.');

            return $response->withRedirect($this->router->pathFor('loginAskEmail'));
        }

        $this->flashMessages->success('Mail odeslán! Klikni na odkaz v mailu a tím se přihlásíš!');

        return $response->withRedirect($this->router->pathFor('loginAfterLinkSent'));
    }

    public function tryLoginWithToken(Request $request, Response $response, array $args) {
        $loginToken = $args['token'];
        if ($this->userService->isLoginTokenValid($loginToken)) {
            $user = $this->userService->getUserFromToken($loginToken);
            $this->userRegeneration->saveUserIdIntoSession($user);
            $this->userService->invalidateAllLoginTokens($user);

            return $response->withRedirect($this->router->pathFor('getDashboard', ['eventSlug' => $user->event->slug]));
        }

        $this->flashMessages->warning('Token pro přihlášení není platný. Nech si prosím poslat nový přihlašovací email.');

        return $response->withRedirect($this->router->pathFor('loginAskEmail', ['email' => $loginToken->user->email]));
    }

    public function logout(Request $request, Response $response, array $args) {
        $this->userService->logoutUser();
        $this->flashMessages->info('Odhlášení bylo úspěšné');

        return $response->withRedirect($this->router->pathFor('landing'));
    }

    public function setRole(Request $request, Response $response, array $args) {
        $user = $request->getAttribute('user');
        $this->userService->setRole($user, $request->getParam('role'));

        return $response->withRedirect($this->router->pathFor('getDashboard', ['eventSlug' => $user->event->slug]));
    }

    public function getDashboard(Request $request, Response $response, array $args) {
        /** @var User */
        $user = $request->getAttribute('user');
        $role = $this->participantService->getUserRole($user);

        $routerEventSlug = ['eventSlug' => $user->event->slug];
        switch ($role) {
            case null:
                return $response->withRedirect($this->router->pathFor('chooseRole', $routerEventSlug));

            case User::ROLE_IST:
                return $response->withRedirect($this->router->pathFor('ist-dashboard', $routerEventSlug));

            case User::ROLE_PATROL_LEADER:
                return $response->withRedirect($this->router->pathFor('pl-dashboard', $routerEventSlug));

            case User::ROLE_GUEST:
                return $response->withRedirect($this->router->pathFor('guest-dashboard', $routerEventSlug));

            default:
                throw new RuntimeException('got unknown role for User id '.$user->id.': '.$role);
        }
    }

    // TODO clear
    protected function trySignup(Request $request, Response $response, array $args) {
        $parameters = $request->getParsedBody();
        $email = $parameters['email'];

        if ($this->userService->isEmailExisting($email)) {
            $this->flashMessages->error('Nepovedlo se založit uživatele pro email '.htmlspecialchars($email,
                    ENT_QUOTES).', protože už takový existuje. Nechceš se spíš přihlásit?');
            if (isset($parameters['eventSlug'])) {
                $pathForRedirect = $this->router->pathFor('landing',
                    ['eventSlug' => $parameters['eventSlug']]);
            } else {
                $pathForRedirect = $this->router->pathFor('kissj-landing');
            }

            return $response->withRedirect($pathForRedirect);
        }

        $user = $this->userService->registerUser($email);
        $this->logger->info('Created new user with email '.$email);

        if (isset($parameters['role'], $parameters['eventSlug'])) {
            // participant signup

            $role = $parameters['role'];
            if (!$this->roleService->isUserRoleNameValid($role)) {
                throw new \RuntimeException('User role "'.$role.'" is not valid');
            }

            $this->roleService->addRole($user, $role);
            /** @var \kissj\Event $event */
            $event = $this->eventService->getEventFromSlug($parameters['eventSlug']);
            try {
                $this->userService->sendLoginTokenByMail(
                    $email,
                    $this->roleService->getReadableRoleName($role),
                    $event->readableName);

                return $response->withRedirect($this->router->pathFor('signupSuccess'));
            } catch (\RuntimeException $e) {
                $this->logger->addError("Error sending registration email to $email to event $event->slug with token ".$this->userService->getTokenForEmail($email),
                    array ($e));
                $this->flashMessages->error('Registrace se povedla, ale nezdařilo se odeslat přihlašovací email. Zkus se prosím přihlásit znovu.');

                return $response->withRedirect($this->router->pathFor('landing',
                    ['eventSlug' => $event->slug]));
            }
        } else {
            // new event registration signup
            try {
                $this->userService->sendLoginTokenByMail($email);

                return $response->withRedirect($this->router->pathFor('kissj-signupSuccess'));
            } catch (\RuntimeException $e) {
                $this->logger->addError("Error sending registration email to $email with token ".$this->userService->getTokenForEmail($email),
                    array ($e));
                $this->flashMessages->error('Registrace se povedla, ale nezdařilo se odeslat přihlašovací email )-:');

                return $response->withRedirect($this->router->pathFor('kissj-landing'));
            }

        }
    }
}
