<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

class ProgrammeAdminsOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $this->tryGetUser($request);

        if ($user !== null && $user->role->isEligibleToManageProgrammes() === false) {
            $this->flashMessages->error('flash.error.programmeAdminOnly');

            return $this->createRedirectResponse($request, 'getDashboard');
        }

        return $handler->handle($request);
    }
}
