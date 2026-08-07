<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\AbstractController;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Event\EventService;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\Participant\ParticipantStatisticsService;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

class AdminEventController extends AbstractController
{
    public const int MAXIMAL_ALLOWED_CAPACITY = 1000000;

    public function __construct(
        private readonly ParticipantRepository $participantRepository,
        private readonly ParticipantService $participantService,
        private readonly ParticipantStatisticsService $participantStatisticsService,
        private readonly EventRepository $eventRepository,
        private readonly EventService $eventService,
    ) {
    }

    public function showDashboard(
        Response $response,
        Event $event,
    ): Response {
        $eventType = $event->getEventType();
        $contingentStatistic = [];
        if ($eventType->showContingentPatrolStats()) {
            $contingentStatistic = $this->participantStatisticsService->getContingentStatistic(
                $event,
                ParticipantRole::PatrolLeader,
                $eventType->getContingents(),
            );
        }

        $istArrivalStatistic = [];
        if ($eventType->getContentArbiterIst()->arrivalDate->allowed) {
            $istArrivalStatistic = $this->participantRepository->getIstArrivalStatistic($event);
        }

        $foodStatistic = [];
        if ($eventType->showFoodStats()) {
            $foodStatistic = $this->participantStatisticsService->getDigestFoodStatistic($event);
        }

        return $this->view->render(
            $response,
            'admin/dashboard-admin.twig',
            [
                'participantsComingCount' => $this->participantService->getParticipantsComingToEventCount($event),
                'maximalClosedParticipantsCount' => $event->maximalClosedParticipantsCount,
                'patrols' => $this->participantStatisticsService->getStatistic($event, ParticipantRole::PatrolLeader),
                'ists' => $this->participantStatisticsService->getStatistic($event, ParticipantRole::Ist),
                'troopLeaders' => $this->participantStatisticsService->getStatistic($event, ParticipantRole::TroopLeader),
                'troopParticipants' => $this->participantStatisticsService->getStatistic($event, ParticipantRole::TroopParticipant),
                'guests' => $this->participantStatisticsService->getStatistic($event, ParticipantRole::Guest),
                'organizingTeam' => $this->participantStatisticsService->getStatistic($event, ParticipantRole::OrganizingTeam),
                'contingentsPatrolStatistic' => $contingentStatistic,
                'istArrivalStatistic' => $istArrivalStatistic,
                'foodStatistic' => $foodStatistic,
                'entryStatistic' => $this->participantRepository->getEntryStatisticGlobal($event),
                'entryStatisticRoles' => $this->participantRepository->getEntryStatisticForAllowedRoles($event),
            ],
        );
    }

    public function showDetailedFoodStats(
        Response $response,
        Event $event,
    ): Response {
        // fetched once and shared below - the day-by-day plan and the on-site matrix both need
        // the same paid-participant set, and hydrating it twice roughly doubles page load cost
        $participants = $this->participantRepository->getAllParticipantsWithStatus(
            ParticipantRole::all(),
            [UserStatus::Paid],
            $event,
        );

        return $this->view->render($response, 'admin/foodStats-admin.twig', [
            'event'  => $event,
            'foodStatistic' => $this->participantStatisticsService
                ->createParticipantFoodPlanFromParticipants($participants, $event, false)
                ->roleAggregatedToArray(),
            'presentFoodStatistic' => $this->participantStatisticsService
                ->getPresentFoodStatisticFromParticipants($participants, ParticipantRole::all()),
        ]);
    }

    public function showOrganizingTeam(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        $organizingTeamParticipants = $this->participantRepository->getAllParticipantsWithStatus(
            [ParticipantRole::OrganizingTeam],
            [UserStatus::Open, UserStatus::Closed, UserStatus::Approved, UserStatus::Paid],
            $event,
        );

        $otRegistrationUrl = null;
        if ($event->allowOrganizingTeam) {
            $otToken = $event->organizingTeamRegistrationToken
                ?? $this->eventRepository->generateNewOrganizingTeamRegistrationToken($event);

            $routeParser = RouteContext::fromRequest($request)->getRouteParser();
            $otRegistrationUrl = $routeParser->fullUrlFor(
                $request->getUri(),
                'loginAskEmail',
                ['eventSlug' => $event->slug],
                ['ot_token' => $otToken]
            );
        }

        return $this->view->render($response, 'admin/organizingTeam-admin.twig', [
            'organizingTeamParticipants' => $organizingTeamParticipants,
            'otRegistrationUrl' => $otRegistrationUrl,
        ]);
    }

    public function regenerateOrganizingTeamToken(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        $this->eventRepository->generateNewOrganizingTeamRegistrationToken($event);

        $this->flashMessages->success('flash.success.otTokenRegenerated');
        $this->logger->info('OT registration token regenerated for event ' . $event->slug);

        return $this->redirect($request, $response, 'admin-organizing-team');
    }

    public function showRoleManagement(
        Response $response,
        Event $event,
    ): Response {
        $eventType = $event->getEventType();

        $roleConfigurations = [];
        foreach (ParticipantRole::all() as $role) {
            $allowedItems = $eventType->getContentArbiterForRole($role)->getAllowedItems();
            $maxFromEventType = $eventType->getMaximalCountForRole($event, $role);
            $dbMax = $this->eventService->getDbMaxForRole($event, $role);

            $roleConfigurations[] = [
                'role' => $role,
                'enabled' => $event->isRoleEnabled($role),
                'allowField' => $this->getAllowFieldForRole($role),
                'maxField' => $this->getMaxFieldForRole($role),
                'allowedItems' => $allowedItems,
                'dbMax' => $dbMax,
                'eventTypeMax' => $maxFromEventType,
                'hasDifference' => $dbMax !== $maxFromEventType,
            ];
        }

        return $this->view->render(
            $response,
            'admin/role-management.twig',
            [
                'roleConfigurations' => $roleConfigurations,
                'maximalClosedParticipantsCount' => $event->maximalClosedParticipantsCount,
                'minimalPatrolParticipantsCount' => $event->minimalPatrolParticipantsCount,
                'maximalPatrolParticipantsCount' => $event->maximalPatrolParticipantsCount,
                'minimalTroopParticipantsCount' => $event->minimalTroopParticipantsCount,
                'maximalTroopParticipantsCount' => $event->maximalTroopParticipantsCount,
            ],
        );
    }

    public function saveRoleManagement(
        Request $request,
        Response $response,
        Event $event,
    ): Response {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            throw new \RuntimeException('getParsedBody() did not return array');
        }

        /** @var string $roleValue */
        $roleValue = $body['role'] ?? '';

        $saved = match ($roleValue) {
            'total' => $this->saveTotal($event, $body),
            'pl' => $this->savePatrolLeader($event, $body),
            'pp' => $this->savePatrolParticipant($event, $body),
            'tl' => $this->saveTroopLeader($event, $body),
            'tp' => $this->saveTroopParticipant($event, $body),
            'ist' => $this->saveIst($event, $body),
            'guest' => $this->saveGuest($event, $body),
            'ot' => $this->saveOrganizingTeam($event, $body),
            default => throw new \RuntimeException('Unknown role: ' . $roleValue),
        };

        if (!$saved) {
            $this->flashMessages->warning('flash.warning.capacityOutOfRange');

            return $this->redirect($request, $response, 'admin-role-management');
        }

        $this->eventRepository->persist($event);

        $this->flashMessages->success('flash.success.roleManagementSaved');

        return $this->redirect($request, $response, 'admin-role-management');
    }

    /**
     * @param array<mixed> $body
     */
    private function saveTotal(Event $event, array $body): bool
    {
        $maximalParticipants = $body['maximalClosedParticipantsCount'] ?? '';
        if (!$this->areCapacitiesValid($maximalParticipants)) {
            return false;
        }

        $event->maximalClosedParticipantsCount = $this->parseNullableInt($maximalParticipants);

        return true;
    }

    /**
     * @param array<mixed> $body
     */
    private function savePatrolLeader(Event $event, array $body): bool
    {
        $maximalPatrols = $body['maximalClosedPatrolsCount'] ?? '';
        if (!$this->areCapacitiesValid($maximalPatrols)) {
            return false;
        }

        $event->allowPatrols = isset($body['allowPatrols']);
        $event->maximalClosedPatrolsCount = $this->parseNullableInt($maximalPatrols);

        return true;
    }

    /**
     * @param array<mixed> $body
     */
    private function savePatrolParticipant(Event $event, array $body): bool
    {
        $minimalParticipants = $body['minimalPatrolParticipantsCount'] ?? '';
        $maximalParticipants = $body['maximalPatrolParticipantsCount'] ?? '';
        if (!$this->areCapacitiesValid($minimalParticipants, $maximalParticipants)) {
            return false;
        }

        $event->minimalPatrolParticipantsCount = $this->parseNullableInt($minimalParticipants);
        $event->maximalPatrolParticipantsCount = $this->parseNullableInt($maximalParticipants);

        return true;
    }

    /**
     * @param array<mixed> $body
     */
    private function saveTroopLeader(Event $event, array $body): bool
    {
        $maximalTroopLeaders = $body['maximalClosedTroopLeadersCount'] ?? '';
        if (!$this->areCapacitiesValid($maximalTroopLeaders)) {
            return false;
        }

        $event->allowTroops = isset($body['allowTroops']);
        $event->maximalClosedTroopLeadersCount = $this->parseNullableInt($maximalTroopLeaders);

        return true;
    }

    /**
     * @param array<mixed> $body
     */
    private function saveTroopParticipant(Event $event, array $body): bool
    {
        $maximalClosedParticipants = $body['maximalClosedTroopParticipantsCount'] ?? '';
        $minimalParticipants = $body['minimalTroopParticipantsCount'] ?? '';
        $maximalParticipants = $body['maximalTroopParticipantsCount'] ?? '';
        if (!$this->areCapacitiesValid($maximalClosedParticipants, $minimalParticipants, $maximalParticipants)) {
            return false;
        }

        $event->maximalClosedTroopParticipantsCount = $this->parseNullableInt($maximalClosedParticipants);
        $event->minimalTroopParticipantsCount = $this->parseNullableInt($minimalParticipants);
        $event->maximalTroopParticipantsCount = $this->parseNullableInt($maximalParticipants);

        return true;
    }

    /**
     * @param array<mixed> $body
     */
    private function saveIst(Event $event, array $body): bool
    {
        $maximalIsts = $body['maximalClosedIstsCount'] ?? '';
        if (!$this->areCapacitiesValid($maximalIsts)) {
            return false;
        }

        $event->allowIsts = isset($body['allowIsts']);
        $event->maximalClosedIstsCount = $this->parseNullableInt($maximalIsts);

        return true;
    }

    /**
     * @param array<mixed> $body
     */
    private function saveGuest(Event $event, array $body): bool
    {
        $maximalGuests = $body['maximalClosedGuestsCount'] ?? '';
        if (!$this->areCapacitiesValid($maximalGuests)) {
            return false;
        }

        $event->allowGuests = isset($body['allowGuests']);
        $event->maximalClosedGuestsCount = $this->parseNullableInt($maximalGuests);

        return true;
    }

    /**
     * @param array<mixed> $body
     */
    private function saveOrganizingTeam(Event $event, array $body): bool
    {
        $maximalOrganizingTeam = $body['maximalClosedOrganizingTeamCount'] ?? '';
        if (!$this->areCapacitiesValid($maximalOrganizingTeam)) {
            return false;
        }

        $event->allowOrganizingTeam = isset($body['allowOrganizingTeam']);
        $event->maximalClosedOrganizingTeamCount = $this->parseNullableInt($maximalOrganizingTeam);

        return true;
    }

    private function getAllowFieldForRole(ParticipantRole $role): string
    {
        return match ($role) {
            ParticipantRole::PatrolLeader, ParticipantRole::PatrolParticipant => 'allowPatrols',
            ParticipantRole::TroopLeader, ParticipantRole::TroopParticipant => 'allowTroops',
            ParticipantRole::Ist => 'allowIsts',
            ParticipantRole::Guest => 'allowGuests',
            ParticipantRole::OrganizingTeam => 'allowOrganizingTeam',
        };
    }

    private function getMaxFieldForRole(ParticipantRole $role): string
    {
        return match ($role) {
            ParticipantRole::PatrolLeader => 'maximalClosedPatrolsCount',
            ParticipantRole::PatrolParticipant => 'maximalPatrolParticipantsCount',
            ParticipantRole::TroopLeader => 'maximalClosedTroopLeadersCount',
            ParticipantRole::TroopParticipant => 'maximalClosedTroopParticipantsCount',
            ParticipantRole::Ist => 'maximalClosedIstsCount',
            ParticipantRole::Guest => 'maximalClosedGuestsCount',
            ParticipantRole::OrganizingTeam => 'maximalClosedOrganizingTeamCount',
        };
    }

    private function areCapacitiesValid(mixed ...$values): bool
    {
        foreach ($values as $value) {
            if (!$this->isCapacityValid($value)) {
                return false;
            }
        }

        return true;
    }

    private function isCapacityValid(mixed $value): bool
    {
        if (!is_string($value) && !is_int($value)) {
            return false;
        }

        $trimmed = trim((string)$value);
        if ($trimmed === '') {
            return true; // empty means unlimited
        }

        if (preg_match('~^\d+$~', $trimmed) !== 1) {
            return false;
        }

        return (int)$trimmed <= self::MAXIMAL_ALLOWED_CAPACITY;
    }

    private function parseNullableInt(mixed $value): ?int
    {
        if (!is_string($value) && !is_int($value)) {
            return null;
        }

        $trimmed = trim((string)$value);
        if ($trimmed === '') {
            return null;
        }

        return (int)$trimmed;
    }
}
