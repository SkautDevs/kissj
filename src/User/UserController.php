<?php

namespace kissj\User;

class UserController {
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
        } else {
            $this->flashMessages->warning('Token pro přihlášení není platný. Nech si prosím poslat nový přihlašovací email.');
            if (isset($args['eventSlug'])) {
                return $response->withRedirect($this->router->pathFor('loginAskEmail'));
            }

            return $response->withRedirect($this->router->pathFor('kissj-landing'));
        }
    }

    public function sendEmail(Request $request, Response $response, array $args) {
        $email = $request->getParam('email');
        if ($this->userService->isEmailExisting($email)) {
            $user = $this->userService->getUserFromEmail($email);
            /** @var \kissj\Event\Event $event */
            if ($event = $request->getAttribute('event')) {
                $role = $this->roleService->getRole($user, $event);
                $readableRole = $this->roleService->getReadableRoleName($role->name);
            } else {
                // TODO needed more elegant solution
                $readableRole = '';
            }
            try {
                $this->userService->sendLoginTokenByMail($email, $readableRole);
            } catch (Exception $e) {
                $this->logger->addError("Error sending login email to $email with token ".
                    $this->userService->getTokenForEmail($email), array ($e));
                $this->flashMessages->error('Nezdařilo se odeslat přihlašovací email. Zkus to prosím znovu.');

                return $response->withRedirect($this->router->pathFor('loginAskEmail'));
            }

            $this->flashMessages->success('Posláno! Klikni na odkaz v mailu a tím se přihlásíš!');

            return $response->withRedirect($this->router->pathFor('landing'));

        }

        $this->flashMessages->error('Pardon, tvůj přihlašovací email tu nemáme. Nechceš se spíš zaregistrovat?');

        return $response->withRedirect($this->router->pathFor('landing'));

    }

    public function logout(Request $request, Response $response, array $args) {
        $this->userService->logoutUser();
        $this->flashMessages->info('Odhlášení bylo úspěšné');

        /** @var \kissj\Event\Event $event */
        if ($event = $request->getAttribute('event')) {
            $pathForRedirect = $this->router->pathFor('landing', ['eventSlug' => $event->slug]);
        } else {
            $pathForRedirect = $this->router->pathFor('kissj-landing');
        }

        return $response->withRedirect($pathForRedirect);
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
            /** @var kissj\Event\Event $event */
            $event = $this->eventService->getEventFromSlug($parameters['eventSlug']);
            try {
                $this->userService->sendLoginTokenByMail(
                    $email,
                    $this->roleService->getReadableRoleName($role),
                    $event->readableName);

                return $response->withRedirect($this->router->pathFor('signupSuccess'));
            } catch (Exception $e) {
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
            } catch (Exception $e) {
                $this->logger->addError("Error sending registration email to $email with token ".$this->userService->getTokenForEmail($email),
                    array ($e));
                $this->flashMessages->error('Registrace se povedla, ale nezdařilo se odeslat přihlašovací email )-:');

                return $response->withRedirect($this->router->pathFor('kissj-landing'));
            }

        }
    }
}
