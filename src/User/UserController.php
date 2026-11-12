<?php

declare(strict_types=1);

namespace kissj\User;

use Exception;
use LogicException;
use kissj\AbstractController;
use kissj\Application\CookieHandler;
use kissj\Event\Event;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\RoleChangeResult;
use kissj\Skautis\SkautisService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController extends AbstractController
{
    public function __construct(
        protected UserRepository $userRepository,
        protected UserService $userService,
        protected UserRegeneration $userRegeneration,
        protected SkautisService $skautisService,
        protected ParticipantService $participantService,
        protected ParticipantRepository $participantRepository,
        protected CookieHandler $cookieHandler,
    ) {
    }

    public function landing(Request $request, Response $response, ?User $user): Response
    {
        if ($user === null) {
            return $this->redirect($request, $response, 'loginAskEmail');
        }

        if ($user->status === UserStatus::WithoutRole) {
            return $this->redirect($request, $response, 'chooseRole');
        }

        return $this->redirect($request, $response, 'getDashboard');
    }

    public function login(Request $request, Response $response, Event $event): Response
    {
        $queryParams = $request->getQueryParams();
        $otToken = $queryParams['ot_token'] ?? null;

        if (
            is_string($otToken)
            && $event->organizingTeamRegistrationToken !== null
            && $otToken === $event->organizingTeamRegistrationToken
        ) {
            $_SESSION['ot_access_granted'] = true;
        }

        $ttlToken = $queryParams['ttl_token'] ?? null;
        if (
            is_string($ttlToken)
            && $event->technicalTeamRegistrationToken !== null
            && $ttlToken === $event->technicalTeamRegistrationToken
        ) {
            $_SESSION['ttl_access_granted'] = $event->id;
        }

        $prefillEmail = $_SESSION['prefill_email'] ?? null;
        unset($_SESSION['prefill_email']);

        $data = [
            'lastLogin' => $this->cookieHandler->getCookie($request, 'lastLogin'),
            'otToken' => $otToken,
            'ttlToken' => is_string($ttlToken) ? $ttlToken : null,
            'prefillEmail' => $prefillEmail,
        ];
        if ($event->getEventType()->isLoginSkautisAllowed()) {
            $data['skautisLoginUri'] = $this->skautisService->getLoginUri($event->slug);
        }

        return $this->view->render(
            $response,
            'kissj/login.twig',
            $data,
        );
    }

    public function sendLoginEmail(Request $request, Response $response): Response
    {
        $email = $this->getParameterFromBody($request, 'email', true);
        $event = $this->getEvent($request);
        $parsedBody = $request->getParsedBody();
        $otTokenRaw = is_array($parsedBody) ? ($parsedBody['ot_token'] ?? null) : null;
        $otToken = is_string($otTokenRaw) ? $otTokenRaw : null;
        $ttlTokenRaw = is_array($parsedBody) ? ($parsedBody['ttl_token'] ?? null) : null;
        $ttlToken = is_string($ttlTokenRaw) ? $ttlTokenRaw : null;

        $caseVariantEmail = $this->userService->findCaseVariantEmail($email, $event);
        if ($caseVariantEmail !== null) {
            $this->flashMessages->warning('flash.warning.emailCaseVariant', ['%email%' => $caseVariantEmail]);
        }

        if (!$this->userRepository->isEmailUserExisting($email, $event)) {
            $this->userService->registerEmailUser($email, $event);
        }

        try {
            $this->userService->sendLoginTokenByMail($email, $request, $event, $otToken, $ttlToken);
        } catch (Exception $e) {
            $this->logger->error('Error sending login email', ['email' => $email, 'exception' => $e]);
            $this->flashMessages->error('flash.error.mailError');

            return $this->redirect($request, $response, 'loginAskEmail');
        }

        $this->flashMessages->success('flash.success.linkSent');

        return $this->redirect($request, $response, 'loginAfterLinkSent');
    }

    public function showAfterLinkSent(Request $request, Response $response): Response
    {
        return $this->view->render($response, 'kissj/login-link-sent.twig');
    }

    public function tryLoginWithToken(Request $request, Response $response, string $token): Response
    {
        if ($this->userService->isLoginTokenValid($token)) {
            $loginToken = $this->userService->getLoginTokenFromStringToken($token);
            $user = $loginToken->user;
            $this->userRegeneration->saveUserIdIntoSession($user);
            $this->userService->invalidateAllLoginTokens($user);
            $response = $this->cookieHandler->setCookie($response, 'lastLogin', 'email');

            $queryParams = $request->getQueryParams();
            $otToken = $queryParams['ot_token'] ?? null;
            if ($otToken !== null && $otToken === $user->event->organizingTeamRegistrationToken) {
                $_SESSION['ot_access_granted'] = true;
            }

            $ttlToken = $queryParams['ttl_token'] ?? null;
            if ($ttlToken !== null && $ttlToken === $user->event->technicalTeamRegistrationToken) {
                $_SESSION['ttl_access_granted'] = $user->event->id;
            }

            return $this->redirect($request, $response, 'landing');
        }

        $loginToken = $this->userService->findLoginTokenByStringToken($token);
        if ($loginToken !== null) {
            $_SESSION['prefill_email'] = $loginToken->user->email;
        }
        $this->flashMessages->warning('flash.warning.invalidLogin');

        return $this->redirect($request, $response, 'loginAskEmail');
    }

    public function logout(Request $request, Response $response): Response
    {
        $this->userService->logoutUser();
        $this->flashMessages->info('flash.info.logout');

        return $this->redirect($request, $response, 'landing');
    }

    public function chooseRole(User $user, Request $request, Response $response): Response
    {
        if ($user->status === UserStatus::Open) {
            return $this->showOwnRoleChange($user, $request, $response);
        }

        $roles = $this->userService->getSelectableRoles($user);
        if (count($roles) === 1) {
            return $this->createParticipantWithRole($user, $roles[0], $request, $response);
        }

        if ($roles === []) {
            $this->flashMessages->info('flash.info.noRoleAvailable');
        }

        return $this->view->render($response, 'kissj/choose-role.twig', [
            'event' => $user->event,
            'roles' => $roles,
            'currentRole' => null,
        ]);
    }

    public function setRole(User $user, Request $request, Response $response): Response
    {
        $postedRole = $this->getParameterFromBody($request, 'role');
        if ($user->status === UserStatus::Open) {
            return $this->changeOwnRole($user, $postedRole, $request, $response);
        }

        $role = $this->userService->findSelectableRole($user, $postedRole);
        if ($role === null) {
            return $this->rejectUnavailableRole($postedRole, $request, $response);
        }

        return $this->createParticipantWithRole($user, $role, $request, $response);
    }

    private function rejectUnavailableRole(string $postedRole, Request $request, Response $response): Response
    {
        $this->flashMessages->error(match ($postedRole) {
            ParticipantRole::OrganizingTeam->value => 'flash.error.otAccessDenied',
            ParticipantRole::TechnicalTeamLeader->value,
            ParticipantRole::TechnicalTeamParticipant->value => 'flash.error.ttlAccessDenied',
            default => 'flash.error.roleNotAvailable',
        });

        return $this->redirect($request, $response, 'chooseRole');
    }

    private function showOwnRoleChange(User $user, Request $request, Response $response): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);
        $block = $this->participantService->findOwnRoleChangeBlock($participant);
        if ($block !== null) {
            return $this->redirectWithOwnRoleChangeWarning($block, $request, $response);
        }

        $currentRole = $participant->getRoleOrFail();
        $roles = $this->userService->getSelectableRolesWithCurrent($user, $currentRole);
        if (count($roles) === 1) {
            return $this->redirect($request, $response, 'getDashboard');
        }

        return $this->view->render($response, 'kissj/choose-role.twig', [
            'event' => $user->event,
            'roles' => $roles,
            'currentRole' => $currentRole,
        ]);
    }

    private function changeOwnRole(User $user, string $postedRole, Request $request, Response $response): Response
    {
        $participant = $this->participantRepository->getParticipantFromUser($user);
        $fromRole = $participant->getRoleOrFail();
        if ($postedRole === $fromRole->value) {
            return $this->redirect($request, $response, 'getDashboard');
        }

        $result = $this->participantService->tryChangeOwnRole($participant, $postedRole);
        if ($result === RoleChangeResult::RoleNotValid) {
            return $this->rejectUnavailableRole($postedRole, $request, $response);
        }

        if ($result !== RoleChangeResult::Success) {
            return $this->redirectWithOwnRoleChangeWarning($result, $request, $response);
        }

        $role = $participant->getRoleOrFail();
        $this->logger->info('Participant changed own role', [
            'participantId' => $participant->id,
            'fromRole' => $fromRole->value,
            'toRole' => $role->value,
        ]);
        $this->flashMessages->success(
            'flash.success.ownRoleChanged',
            ['%role%' => $this->translator->trans('role.' . $role->value)],
        );

        return $this->redirect($request, $response, 'getDashboard');
    }

    private function redirectWithOwnRoleChangeWarning(
        RoleChangeResult $result,
        Request $request,
        Response $response,
    ): Response {
        $this->flashMessages->warning(match ($result) {
            RoleChangeResult::PatrolHasParticipants,
            RoleChangeResult::TroopHasParticipants,
            RoleChangeResult::TechnicalTeamHasParticipants => 'flash.warning.ownRoleLeaderHasParticipants',
            RoleChangeResult::TroopParticipantInTroop => $result->value,
            RoleChangeResult::NotOpen => 'flash.warning.ownRoleNotOpen',
            RoleChangeResult::Success,
            RoleChangeResult::SameRole,
            RoleChangeResult::RoleNotValid => throw new LogicException(
                'Unexpected own role change result ' . $result->name,
            ),
        });

        return $this->redirect($request, $response, 'getDashboard');
    }

    private function createParticipantWithRole(
        User $user,
        ParticipantRole $role,
        Request $request,
        Response $response,
    ): Response {
        $participant = $this->userService->createParticipantSetRole($user, $role->value);

        if ($user->loginType === UserLoginType::Skautis) {
            $this->skautisService->prefillDataFromSkautis($participant);
        }

        return $this->redirect($request, $response, 'getDashboard');
    }

    public function getDashboard(User $user, Request $request, Response $response): Response
    {
        $routerEventSlug = ['eventSlug' => $user->event->slug];

        return match ($user->role) {
            UserRole::Participant => $this->redirect($request, $response, 'dashboard', $routerEventSlug),
            UserRole::ProgrammeAdmin => $this->redirect($request, $response, 'admin-programmes', $routerEventSlug),
            UserRole::Admin,
            UserRole::IstAdmin,
            UserRole::ContingentAdminCs,
            UserRole::ContingentAdminSk,
            UserRole::ContingentAdminPl,
            UserRole::ContingentAdminHu,
            UserRole::ContingentAdminEu,
            UserRole::ContingentAdminRo,
            => $this->redirect($request, $response, 'admin-dashboard', $routerEventSlug),
        };
    }

    public function showLoginHelp(Response $response): Response
    {
        return $this->view->render($response, 'kissj/login-help.twig');
    }
}
