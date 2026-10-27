<?php

declare(strict_types=1);

namespace kissj\Middleware;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as ResponseHandler;

abstract class AbstractRoleOnlyMiddleware extends BaseMiddleware
{
    public function __construct(
        private readonly FlashMessagesInterface $flashMessages,
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    abstract protected function getRole(): ParticipantRole;

    abstract protected function getFlashKey(): string;

    public function process(Request $request, ResponseHandler $handler): Response
    {
        $user = $this->tryGetUser($request);

        if (
            $user !== null
            && $this->participantRepository->getParticipantFromUser($user)->role !== $this->getRole()
        ) {
            $this->flashMessages->error($this->getFlashKey());

            return $this->createRedirectResponse($request, 'landing');
        }

        return $handler->handle($request);
    }
}
