<?php

namespace kissj\Participant\FreeParticipant;

use kissj\AbstractController;
use kissj\Payment\PaymentService;
use kissj\User\User;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class FreeParticipantController extends AbstractController {
    private $freeParticipantService;
    private $freeParticipantRepository;
    private $paymentService;

    public function __construct(
        FreeParticipantService $freeParticipantService,
        FreeParticipantRepository $freeParticipantRepository,
        PaymentService $paymentService
    ) {
        $this->freeParticipantService = $freeParticipantService;
        $this->freeParticipantRepository = $freeParticipantRepository;
        $this->paymentService = $paymentService;
    }

    public function showDashboard(Response $response, User $user): ResponseInterface {
        $freeParticipant = $this->freeParticipantService->getFreeParticipant($user);
        $possibleOnePayment = $this->paymentService->findLastPayment($freeParticipant);

        return $this->view->render($response, 'dashboard-fp.twig',
            ['user' => $user, 'fp' => $freeParticipant, 'payment' => $possibleOnePayment]);
    }

    public function showDetailsChangeable(User $user, Response $response): ResponseInterface {
        $fpDetails = $this->freeParticipantService->getFreeParticipant($user);

        return $this->view->render($response, 'changeDetails-fp.twig',
            ['fp' => $fpDetails]);
    }

    public function changeDetails(Request $request, Response $response): ResponseInterface {
        $freeParticipant = $this->freeParticipantService->addParamsIntoFreeParticipant(
            $this->freeParticipantService->getFreeParticipant($request->getAttribute('user')),
            $request->getParams()
        );

        $this->freeParticipantRepository->persist($freeParticipant);
        $this->flashMessages->success('Details successfully saved. ');

        return $response->withRedirect($this->router->urlFor('fp-dashboard',
            ['eventSlug' => $freeParticipant->user->event->slug]));
    }

    public function showCloseRegistration(User $user, Response $response): ResponseInterface {
        $freeParticipant = $this->freeParticipantService->getFreeParticipant($user);
        $validRegistration = $this->freeParticipantService->isCloseRegistrationValid($freeParticipant); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-fp.twig',
                ['dataProtectionUrl' => $freeParticipant->user->event->dataProtectionUrl]);
        }

        return $response->withRedirect($this->router->urlFor('fp-dashboard',
            ['eventSlug' => $freeParticipant->user->event->slug]
        ));
    }

    public function closeRegistration(User $user, Response $response): ResponseInterface {
        $freeParticipant = $this->freeParticipantService->getFreeParticipant($user);
        $freeParticipant = $this->freeParticipantService->closeRegistration($freeParticipant);

        if ($freeParticipant->user->status === User::STATUS_CLOSED) {
            $this->flashMessages->success('Registration successfully locked and sent');
            $this->logger->info('Locked registration for Free Participant with ID '.$freeParticipant->id.', user ID '.$freeParticipant->user->id);
        } else {
            $this->flashMessages->error($this->translator->trans('flash.error.wrongData'));
        }

        return $response->withRedirect($this->router->urlFor('fp-dashboard',
            ['eventSlug' => $freeParticipant->user->event->slug]));
    }

    public function showOpenFreeParticipant(int $fpId, Response $response): ResponseInterface {
        $freeParticipatn = $this->freeParticipantRepository->find($fpId);

        return $this->view->render($response, 'admin/openFp-admin.twig', ['fp' => $freeParticipatn]);
    }

    public function openFreeParticipant(int $fpId, Request $request, Response $response): ResponseInterface {
        $reason = htmlspecialchars($request->getParam('reason'), ENT_QUOTES);
        /** @var FreeParticipant $freeParticipant */
        $freeParticipant = $this->freeParticipantRepository->find($fpId);
        $this->freeParticipantService->openRegistration($freeParticipant, $reason);
        $this->flashMessages->info('Free Participant participant denied, email successfully sent');
        $this->logger->info('Denied registration for Free Participant with ID '.$freeParticipant->id.' with reason: '.$reason);

        return $response->withRedirect(
            $this->router->urlFor('admin-show-approving', ['eventSlug' => $freeParticipant->user->event->slug])
        );
    }

    public function mailWelcomeFreeParticipant(int $fpId, Response $response): ResponseInterface {
        $freeParticipant = $this->freeParticipantRepository->find($fpId);
        $this->freeParticipantService->sendWelcome($freeParticipant);
        $this->flashMessages->info('Welcome mail sent to Free Participant participant');
        $this->logger->info('Sent welcome mail to Free Participant participant with ID '.$freeParticipant->id);

        return $response->withRedirect(
            $this->router->urlFor('admin-show-approving', ['eventSlug' => $freeParticipant->user->event->slug])
        );
    }

    public function approveFreeParticipant(int $fpId, Response $response): ResponseInterface {
        /** @var FreeParticipant $freeParticipant */
        $freeParticipant = $this->freeParticipantRepository->find($fpId);
        $this->freeParticipantService->approveRegistration($freeParticipant);
        $this->flashMessages->success('Free Participant participant is approved, payment is generated and mail sent');
        $this->logger->info('Approved registration for Free Participant with ID '.$freeParticipant->id);

        return $response->withRedirect($this->router->urlFor(
            'admin-show-approving', ['eventSlug' => $freeParticipant->user->event->slug])
        );
    }
}
