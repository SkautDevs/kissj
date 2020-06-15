<?php

namespace kissj\Participant\Ist;

use kissj\AbstractController;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

class IstController extends AbstractController {
    private $istService;
    private $istRepository;

    public function __construct(
        IstService $istService,
        IstRepository $istRepository
    ) {
        $this->istService = $istService;
        $this->istRepository = $istRepository;
    }

    public function showDashboard(Response $response, User $user): Response {
        return $this->view->render(
            $response,
            'dashboard-ist.twig',
            ['user' => $user, 'ist' => $this->istService->getIst($user)]
        );
    }

    public function showDetailsChangeable(Request $request, Response $response): Response {
        $istDetails = $this->istService->getIst($request->getAttribute('user'));

        return $this->view->render($response, 'changeDetails-ist.twig',
            ['istDetails' => $istDetails]);
    }

    public function changeDetails(Request $request, Response $response): Response {
        $ist = $this->istService->getIst($request->getAttribute('user'));

        $uploadedFiles = $request->getUploadedFiles();
        if (!array_key_exists('uploadFile', $uploadedFiles) || !$uploadedFiles['uploadFile'] instanceof UploadedFile) {
            // problem - too big file -> not safe anything, because always got nulls in request fields
            $this->flashMessages->warning($this->translator->trans('flash.warning.fileTooBig'));

            return $this->redirect($request, $response, 'ist-dashboard', ['eventSlug' => $ist->user->event->slug]);
        }

        $errorNum = $uploadedFiles['uploadFile']->getError();
        if ($errorNum === UPLOAD_ERR_OK) {
            $ist = $this->istService->handleUploadedFile($ist, $uploadedFiles['uploadFile']);
        } elseif ($errorNum === UPLOAD_ERR_INI_SIZE) {
            $this->flashMessages->warning($this->translator->trans('flash.warning.fileTooBig'));
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
