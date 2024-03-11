<?php

declare(strict_types=1);

namespace kissj\Settings;

use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;
use kissj\User\UserStatus;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class TwigExtension extends AbstractExtension
{
    /**
     * @return TwigTest[]
     */
    public function getTests(): array
    {
        return [
            new TwigTest(
                'PatrolLeader',
                fn ($participant): bool => $participant instanceof PatrolLeader
            ),
            new TwigTest(
                'PatrolParticipant',
                fn ($participant): bool => $participant instanceof PatrolParticipant
            ),
            new TwigTest(
                'TroopLeader',
                fn ($participant): bool => $participant instanceof TroopLeader
            ),
            new TwigTest(
                'TroopParticipant',
                fn ($participant): bool => $participant instanceof TroopParticipant
            ),
            new TwigTest(
                'Leader',
                fn ($participant): bool => $participant instanceof PatrolLeader || $participant instanceof TroopLeader
            ),
            new TwigTest(
                'Troop',
                fn ($participant): bool => $participant instanceof TroopLeader || $participant instanceof TroopParticipant
            ),
            new TwigTest(
                'hasUser',
                fn ($participant): bool => !$participant instanceof PatrolParticipant
            ),
            new TwigTest(
                'eligibleForShowTieCode',
                fn ($participant): bool => (
                    $participant instanceof TroopLeader && $participant->getUserButNotNull()->status === UserStatus::Open
                ) || (
                    $participant instanceof TroopParticipant && $participant->troopLeader === null
                )
            ),
        ];
    }
}
