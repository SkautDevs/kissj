<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use Tests\AppTestCase;

class ParticipantTieCodeUniquenessTest extends AppTestCase
{
    public function testCreatedParticipantHasSixUppercaseLetterTieCode(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $participant = $this->createParticipantWithRole($app->getContainer(), 'ist', $event);

        self::assertMatchesRegularExpression(
            self::TIE_CODE_PATTERN,
            $participantRepository->getOneBy(['id' => $participant->id])->tieCode,
        );
    }

    public function testEnsureUniqueTieCodeRegeneratesOnCollision(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $existingCode = $this->createParticipantWithRole($app->getContainer(), 'ist', $event)->tieCode;

        // the pre-check is only allowed on a detached entity, so build one instead of re-fetching
        $colliding = new Participant();
        $colliding->tieCode = $existingCode;

        $participantRepository->ensureUniqueTieCode($colliding);

        self::assertNotSame($existingCode, $colliding->tieCode);
        self::assertMatchesRegularExpression(self::TIE_CODE_PATTERN, $colliding->tieCode);
        self::assertFalse($participantRepository->isTieCodeInUse($colliding->tieCode));
    }
}
