<?php

namespace kissj\Participant\Ist;

use kissj\AbstractController;
use kissj\Event\ContentArbiterIst;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class IstController extends AbstractController {
    private IstService $istService;
    private IstRepository $istRepository;
    private ContentArbiterIst $contentArbiterIst;

    public function __construct(
        IstService $istService,
        IstRepository $istRepository,
        ContentArbiterIst $contentArbiterIst
    ) {
        $this->istService = $istService;
        $this->istRepository = $istRepository;
        $this->contentArbiterIst = $contentArbiterIst;
    }

    public function showDashboard(Response $response, User $user): Response {
        return $this->view->render(
            $response,
            'dashboard-ist.twig',
            ['user' => $user, 'ist' => $this->istService->getIst($user), 'ca' => $this->contentArbiterIst]
        );
    }

    public function showDetailsChangeable(Request $request, Response $response): Response {
        $istDetails = $this->istService->getIst($request->getAttribute('user'));

        return $this->view->render(
            $response,
            'changeDetails-ist.twig',
            ['istDetails' => $istDetails, 'ca' => $this->contentArbiterIst]
        );
    }

    public function changeDetails(Request $request, Response $response): Response {
        $ist = $this->istService->getIst($request->getAttribute('user'));

        if ($this->contentArbiterIst->uploadFile) {
            $uploadedFile = $this->resolveUploadedFiles($request->getUploadedFiles());
            if ($uploadedFile === null) {
                return $this->redirect($request, $response, 'ist-dashboard', ['eventSlug' => $ist->user->event->slug]);
            }

            $this->istService->saveFileTo($ist, $uploadedFile);
        }

        $ist = $this->istService->addParamsIntoIst($ist, $request->getParsedBody());

        $this->istRepository->persist($ist);
        $this->flashMessages->success($this->translator->trans('flash.success.detailsSaved'));

        return $this->redirect($request, $response, 'ist-dashboard', ['eventSlug' => $ist->user->event->slug]);
    }

    public function showCloseRegistration(Request $request, Response $response): Response {
        $ist = $this->istService->getIst($request->getAttribute('user')); // TODO change to autowiring
        $validRegistration = $this->istService->isCloseRegistrationValid($ist); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-ist.twig',
                ['dataProtectionUrl' => $ist->user->event->dataProtectionUrl]);
        }

        return $this->redirect($request, $response, 'ist-dashboard', ['eventSlug' => $ist->user->event->slug]);
    }

    public function closeRegistration(Request $request, Response $response): Response {
        $ist = $this->istService->getIst($request->getAttribute('user'));
        $ist = $this->istService->closeRegistration($ist);

        if ($ist->user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success($this->translator->trans('flash.success.locked'));
            $this->logger->info('Locked registration for IST with ID '.$ist->id.', user ID '.$ist->user->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $this->redirect($request, $response, 'ist-dashboard', ['eventSlug' => $ist->user->event->slug]);
    }

    public function showOpenIst(int $istId, Response $response): Response {
        $ist = $this->istRepository->find($istId);

        return $this->view->render($response, 'admin/openIst-admin.twig', ['ist' => $ist]);
    }

    public function openIst(int $istId, Request $request, Response $response): Response {
        $reason = htmlspecialchars($request->getParsedBody()['reason'], ENT_QUOTES);
        /** @var Ist $ist */
        $ist = $this->istRepository->find($istId);
        $this->istService->openRegistration($ist, $reason);
        $this->flashMessages->info($this->translator->trans('flash.info.istDenied'));
        $this->logger->info('Denied registration for IST with ID '.$ist->id.' with reason: '.$reason);

        return $this->redirect($request, $response, 'admin-show-approving', ['eventSlug' => $ist->user->event->slug]);
    }

    public function approveIst(int $istId, Request $request, Response $response): Response {
        /** @var Ist $ist */
        $ist = $this->istRepository->find($istId);
        $this->istService->approveRegistration($ist);
        $this->flashMessages->success($this->translator->trans('flash.success.approved'));
        $this->logger->info('Approved registration for IST with ID '.$ist->id);

        return $this->redirect($request, $response, 'admin-show-approving', ['eventSlug' => $ist->user->event->slug]);
    }
}
