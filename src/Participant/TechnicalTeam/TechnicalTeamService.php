<?php

declare(strict_types=1);

namespace kissj\Participant\TechnicalTeam;

use kissj\User\User;

readonly class TechnicalTeamService
{
    public function __construct(
        private TechnicalTeamLeaderRepository $technicalTeamLeaderRepository,
        private TechnicalTeamParticipantRepository $technicalTeamParticipantRepository,
    ) {
    }

    public function getTechnicalTeamLeader(User $user): TechnicalTeamLeader
    {
        return $this->technicalTeamLeaderRepository->getOneBy(['user' => $user]);
    }

    public function getTechnicalTeamParticipant(int $participantId): TechnicalTeamParticipant
    {
        return $this->technicalTeamParticipantRepository->get($participantId);
    }

    public function findTechnicalTeamParticipant(int $participantId): ?TechnicalTeamParticipant
    {
        $participant = $this->technicalTeamParticipantRepository->findOneBy(['id' => $participantId]);

        return $participant instanceof TechnicalTeamParticipant ? $participant : null;
    }

    public function deleteTechnicalTeamParticipant(TechnicalTeamParticipant $participant): void
    {
        $this->technicalTeamParticipantRepository->delete($participant);
    }

    public function belongsToLeader(TechnicalTeamParticipant $participant, TechnicalTeamLeader $leader): bool
    {
        return $participant->technicalTeamLeader->id === $leader->id;
    }
}
