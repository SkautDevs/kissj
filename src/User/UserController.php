<?php

namespace kissj\User;

use kissj\AbstractController;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class UserController extends AbstractController {
    /** @var UserService */
    protected $userService;

    public function __construct(ContainerInterface $c) {
        $this->userService = $c->get('userService');
        parent::__construct($c);
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

    public function tryLogin(Request $request, Response $response, array $args) {
        $loginToken = $args['token'];
        if ($this->userService->isLoginTokenValid($loginToken)) {
            $user = $this->userService->getUserFromToken($loginToken);
            $this->userRegeneration->saveUserIdIntoSession($user);
            $this->userService->invalidateAllLoginTokens($user);
            if (isset($args['eventSlug'])) {
                return $response->withRedirect($this->router->pathFor('getDashboard',
                    ['eventSlug' => $args['eventSlug']]));
            }

            return $response->withRedirect($this->router->pathFor('createEvent'));
        }

        $this->flashMessages->warning('Token pro přihlášení není platný. Nech si prosím poslat nový přihlašovací email.');
        if (isset($args['eventSlug'])) {
            return $response->withRedirect($this->router->pathFor('loginAskEmail'));
        }

        return $response->withRedirect($this->router->pathFor('landing'));
    }

    public function logout(Request $request, Response $response, array $args) {
        $this->userService->logoutUser();
        $this->flashMessages->info('Odhlášení bylo úspěšné');

        return $response->withRedirect($this->router->pathFor('landing'));
    }

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
