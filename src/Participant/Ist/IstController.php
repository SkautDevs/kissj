<?php declare(strict_types=1);

namespace kissj\Participant\Ist;

use kissj\AbstractController;
use kissj\Participant\ParticipantService;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

class IstController extends AbstractController
{
    public function __construct(
        private IstService $istService,
        private IstRepository $istRepository,
        private ParticipantService $participantService,
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

    public function showDetailsChangeable(Response $response, User $user): Response
    {
        $ist = $this->istService->getIst($user);

        return $this->view->render(
            $response,
            'changeDetails-ist.twig',
            [
                'istDetails' => $ist,
                'ca' => $user->event->eventType->getContentArbiterIst(),
            ],
        );
    }

    public function changeDetails(Request $request, Response $response, User $user): Response
    {
        $ist = $this->istService->getIst($user);

        if ($user->event->getEventType()->getContentArbiterIst()->uploadFile) {
            $uploadedFile = $this->resolveUploadedFiles($request);
            if ($uploadedFile instanceof UploadedFile) {
                $this->fileHandler->saveFileTo($ist, $uploadedFile);
            }
        }

        /** @var string[] $parsed */
        $parsed = $request->getParsedBody();
        $ist = $this->istService->addParamsIntoIst($ist, $parsed);

        $this->istRepository->persist($ist);
        $this->flashMessages->success($this->translator->trans('flash.success.detailsSaved'));

        return $this->redirect($request, $response, 'ist-dashboard');
    }

    public function showCloseRegistration(Request $request, Response $response, User $user): Response
    {
        $ist = $this->istService->getIst($user);
        $validRegistration = $this->istService->isCloseRegistrationValid($ist); // call because of warnings
        if ($validRegistration) {
            return $this->view->render(
                $response,
                'closeRegistration-ist.twig',
                ['dataProtectionUrl' => $user->event->dataProtectionUrl]
            );
        }

        return $this->redirect($request, $response, 'ist-dashboard');
    }

    // TODO join into admin
    public function closeRegistration(Request $request, Response $response, User $user): Response
    {
        $ist = $this->istService->getIst($user);
        $ist = $this->istService->closeRegistration($ist);

        if ($user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success($this->translator->trans('flash.success.locked'));
            $this->logger->info('Locked registration for IST with ID ' . $ist->id . ', user ID ' . $user->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $this->redirect($request, $response, 'ist-dashboard');
    }

    // TODO join into admin
    public function approveIst(int $istId, Request $request, Response $response): Response
    {
        /** @var Ist $ist */
        $ist = $this->istRepository->get($istId);
        $this->participantService->approveRegistration($ist);
        $this->flashMessages->success($this->translator->trans('flash.success.istApproved'));
        $this->logger->info('Approved registration for IST with ID ' . $ist->id);

        return $this->redirect($request, $response, 'admin-show-approving');
    }
}
