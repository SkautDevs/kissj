<?php declare(strict_types=1);

namespace kissj\Participant\Ist;

use kissj\AbstractController;
use kissj\Participant\ParticipantService;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IstController extends AbstractController
{
    public function __construct(
        private ParticipantService $participantService,
        private IstService $istService,
    ) {
    }

    public function showDashboard(Response $response, User $user): Response
    {
        $ist = $this->istService->getIst($user);

        return $this->view->render(
            $response,
            'dashboard-ist.twig',
            [
                'user' => $user,
                'ist' => $ist,
                'person' => $ist,
                'ca' => $user->event->eventType->getContentArbiterIst(),
            ],
        );
    }

    public function showCloseRegistration(Request $request, Response $response, User $user): Response
    {
        $ist = $this->istService->getIst($user);
        $validRegistration = $this->participantService->isCloseRegistrationValid($ist); // call because of warnings
        if ($validRegistration) {
            return $this->view->render(
                $response,
                'closeRegistration-ist.twig',
                ['dataProtectionUrl' => $user->event->dataProtectionUrl]
            );
        }

        return $this->redirect($request, $response, 'ist-dashboard');
    }

    public function closeRegistration(Request $request, Response $response, User $user): Response
    {
        $ist = $this->istService->getIst($user);
        $ist = $this->participantService->closeRegistration($ist);
        $istUser = $ist->getUserButNotNull();

        if ($istUser->status === User::STATUS_CLOSED) {
            $this->flashMessages->success($this->translator->trans('flash.success.locked'));
            $this->logger->info('Locked registration for IST with ID ' . $ist->id . ', user ID ' . $istUser->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $this->redirect($request, $response, 'ist-dashboard');
    }
}
