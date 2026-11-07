<?php

namespace kissj\Event;

use kissj\AbstractController;
use kissj\Application\DateTimeUtils;
use kissj\Event\DTO\EventStatsCapacityDTO;
use kissj\Event\DTO\EventStatsDTO;
use kissj\Event\DTO\EventStatsEventDTO;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantService;
use kissj\User\UserStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EventController extends AbstractController
{
    private const array STATS_ROLE_KEYS = [
        'patrols' => [ParticipantRole::PatrolLeader],
        'troops' => [ParticipantRole::TroopLeader, ParticipantRole::TroopParticipant],
        'ists' => [ParticipantRole::Ist],
        'guests' => [ParticipantRole::Guest],
    ];

    public function __construct(
        private readonly EventRepository $eventRepository,
        private readonly ParticipantService $participantService,
        private readonly ParticipantRepository $participantRepository,
    ) {
    }

    public function list(Response $response): Response
    {
        return $this->view->render(
            $response,
            'event/landing.twig',
            [
                'events' => $this->eventRepository->findActiveEvents(),
                'allEvents' => $this->eventRepository->findAll(),
            ],
        );
    }

    public function landingPrettyUrl(string $eventSlug, Request $request, Response $response): Response
    {
        $event = $this->eventRepository->findBySlug($eventSlug);
        if ($event === null) {
            return $this->redirect($request, $response, 'eventList');
        }

        return $this->redirect($request, $response, 'getDashboard', ['eventSlug' => $event->slug]);
    }

    public function stats(Response $response, Event $event): Response
    {
        $total = new EventStatsCapacityDTO(
            $this->participantService->getParticipantsComingToEventCount($event),
            $event->maximalClosedParticipantsCount,
        );
        $isCountedPerContingent = $event->getEventType()->isCapacityCountedPerContingent();
        $capacity = ['total' => $total];
        foreach (self::STATS_ROLE_KEYS as $key => $roles) {
            if ($event->isRoleEnabled($roles[0]) === false) {
                continue;
            }

            $capacity[$key] = new EventStatsCapacityDTO(
                $this->participantRepository->getParticipantsCount(
                    $roles,
                    [UserStatus::Closed, UserStatus::Approved, UserStatus::Paid],
                    $event,
                ),
                $isCountedPerContingent ? null : $this->sumMaximalCounts($event, $roles),
            );
        }

        return $this->getResponseWithJson(
            $response,
            new EventStatsDTO(
                new EventStatsEventDTO($event->readableName),
                $capacity,
                $this->participantRepository->countEntered($event),
                DateTimeUtils::formatAtom(DateTimeUtils::getDateTime()),
            ),
        )
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Cache-Control', 'public, max-age=60');
    }

    /**
     * @param list<ParticipantRole> $roles
     */
    private function sumMaximalCounts(Event $event, array $roles): ?int
    {
        $maximalCounts = array_map(
            fn (ParticipantRole $role): ?int => $event->getEventType()->getMaximalCountForRole($event, $role),
            $roles,
        );

        return in_array(null, $maximalCounts, true) ? null : array_sum($maximalCounts);
    }
}
