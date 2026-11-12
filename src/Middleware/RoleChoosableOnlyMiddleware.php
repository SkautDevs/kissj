<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;
use kissj\User\UserRole;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class RoleChoosableOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $this->tryGetUser($request);

        if ($user !== null && $this->isRoleChoosable($user) === false) {
            $this->flashMessages->warning('flash.warning.roleChoosed');

            return $this->createRedirectResponse($request, 'landing');
        }

        return $handler->handle($request);
    }

    private function isRoleChoosable(User $user): bool
    {
        return $user->status === UserStatus::WithoutRole
            || ($user->status === UserStatus::Open && $user->role === UserRole::Participant);
    }
}
