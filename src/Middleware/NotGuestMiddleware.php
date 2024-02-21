<?php

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotGuestMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
        private readonly TranslatorInterface $translator,
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $request->getAttribute('user');

        if (
            $user instanceof User
            && $this->participantRepository->getParticipantFromUser($user)->role === ParticipantRole::Guest
        ) {
            $this->flashMessages->error($this->translator->trans('flash.error.notGuest'));

            return $this->createRedirectResponse($request, 'landing');
        }

        return $handler->handle($request);
    }
}
