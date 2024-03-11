<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminPaymentsOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $request->getAttribute('user');

        if ($user instanceof User && $user->role->isEligibleToConfirmPayments() === false) {
            $this->flashMessages->error($this->translator->trans('flash.error.adminPaymentsOnly'));

            return $this->createRedirectResponse($request, 'getDashboard');
        }

        return $handler->handle($request);
    }
}
