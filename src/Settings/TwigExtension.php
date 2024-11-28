<?php

declare(strict_types=1);

namespace kissj\Settings;

use kissj\Participant\Participant;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;
use kissj\User\UserStatus;
use kissj\Event\Event;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions() :array
    {
        return [
            new TwigFunction('eventLogo', [Event::class, 'getFullLogoUrl']),
        ];
    }

    /**
     * @return TwigTest[]
     */
    public function getTests(): array
    {
        return [
            new TwigTest(
                'PatrolLeader',
                fn (Participant $participant): bool => $participant instanceof PatrolLeader
            ),
            new TwigTest(
                'PatrolParticipant',
                fn (Participant $participant): bool => $participant instanceof PatrolParticipant
            ),
            new TwigTest(
                'TroopLeader',
                fn (Participant $participant): bool => $participant instanceof TroopLeader
            ),
            new TwigTest(
                'TroopParticipant',
                fn (Participant $participant): bool => $participant instanceof TroopParticipant
            ),
            new TwigTest(
                'Leader',
                fn (Participant $participant): bool => $participant instanceof PatrolLeader || $participant instanceof TroopLeader
            ),
            new TwigTest(
                'Troop',
                fn (Participant $participant): bool => $participant instanceof TroopLeader || $participant instanceof TroopParticipant
            ),
            new TwigTest(
                'hasUser',
                fn (Participant $participant): bool => !$participant instanceof PatrolParticipant
            ),
            new TwigTest(
                'eligibleForShowTieCode',
                fn (Participant $participant): bool => (
                    $participant instanceof TroopLeader && $participant->getUserButNotNull()->status === UserStatus::Open
                ) || (
                    // TroopLeader TieCode is also used for pairing with some external services, so they have to be able to access it in paid status
                    $participant instanceof TroopLeader && $participant->getUserButNotNull()->status === UserStatus::Paid
                ) || (
                    $participant instanceof TroopParticipant && $participant->troopLeader === null
                )
            ),
        ];
    }
}
