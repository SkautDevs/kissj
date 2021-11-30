<?php

namespace kissj\Participant\Ist;

use kissj\AbstractController;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

class IstController extends AbstractController
{
    public function __construct(
        private IstService $istService,
        private IstRepository $istRepository
    ) {
    }

    public function showDashboard(Response $response, User $user): Response
    {
        return $this->view->render(
            $response,
            'dashboard-ist.twig',
            [
                'user' => $user,
                'ist' => $this->istService->getIst($user),
                'ca' => $user->event->eventType->getContentArbiterIst(),
            ],
        );
    }

    public function showDetailsChangeable(Request $request, Response $response, User $user): Response
    {
        $istDetails = $this->istService->getIst($user);

        return $this->view->render(
            $response,
            'changeDetails-ist.twig',
            ['istDetails' => $istDetails, 'ca' => $istDetails->user->event->eventType->getContentArbiterIst()]
        );
    }

    public function changeDetails(Request $request, Response $response, User $user): Response
    {
        $ist = $this->istService->getIst($user);

        if ($ist->user->event->getEventType()->getContentArbiterIst()->uploadFile) {
            $uploadedFile = $this->resolveUploadedFiles($request);
            if ($uploadedFile instanceof UploadedFile) {
                $this->fileHandler->saveFileTo($ist, $uploadedFile);
            }
        }

        $ist = $this->istService->addParamsIntoIst($ist, $request->getParsedBody());

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
    public function closeRegistration(Request $request, Response $response): Response
    {
        $ist = $this->istService->getIst($request->getAttribute('user'));
        $ist = $this->istService->closeRegistration($ist);

        if ($ist->user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success($this->translator->trans('flash.success.locked'));
            $this->logger->info('Locked registration for IST with ID ' . $ist->id . ', user ID ' . $ist->user->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $this->redirect($request, $response, 'ist-dashboard', ['eventSlug' => $ist->user->event->slug]);
    }

    // TODO join into admin
    public function approveIst(int $istId, Request $request, Response $response): Response
    {
        /** @var Ist $ist */
        $ist = $this->istRepository->get($istId);
        $this->istService->approveRegistration($ist);
        $this->flashMessages->success($this->translator->trans('flash.success.istApproved'));
        $this->logger->info('Approved registration for IST with ID ' . $ist->id);

        return $this->redirect($request, $response, 'admin-show-approving', ['eventSlug' => $ist->user->event->slug]);
    }
}
