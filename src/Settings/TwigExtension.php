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
            new TwigTest('PatrolLeader', function ($participant): bool {
                return $participant instanceof PatrolLeader;
            }),
            new TwigTest('TroopLeader', function ($participant): bool {
                return $participant instanceof TroopLeader;
            }),
            new TwigTest('TroopParticipant', function ($participant): bool {
                return $participant instanceof TroopParticipant;
            }),
            new TwigTest('Leader', function ($participant): bool {
                return $participant instanceof PatrolLeader || $participant instanceof TroopLeader;
            }),
            new TwigTest('Troop', function ($participant): bool {
                return $participant instanceof TroopLeader || $participant instanceof TroopParticipant;
            }),
            new TwigTest('hasUser', function ($participant): bool {
                return !$participant instanceof PatrolParticipant;
            }),
            new TwigTest('eligibleForShowTieCode', function ($participant): bool {
                return (
                    $participant instanceof TroopLeader && $participant->getUserButNotNull()->status === UserStatus::Open
                ) || (
                    $participant instanceof TroopParticipant && $participant->troopLeader === null
                );
            }),
        ];
    }
}
