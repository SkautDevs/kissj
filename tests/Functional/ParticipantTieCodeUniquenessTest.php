<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\ParticipantRepository;
use kissj\User\UserService;
use LogicException;
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

    public function testPersistingParticipantWithTakenTieCodeRegeneratesIt(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $existingCode = $this->createParticipantWithRole($app->getContainer(), 'ist', $event)->tieCode;
        $user = $this->getService($app, UserService::class)->registerEmailUser(
            'tie-code-hook-' . bin2hex(random_bytes(6)) . '@example.com',
            $event,
        );

        $colliding = new Ist();
        $colliding->user = $user;
        $colliding->role = ParticipantRole::Ist;
        $colliding->tieCode = $existingCode;
        $this->getService($app, IstRepository::class)->persist($colliding);

        $storedCode = $participantRepository->getOneBy(['id' => $colliding->id])->tieCode;
        self::assertNotSame($existingCode, $storedCode);
        self::assertMatchesRegularExpression(self::TIE_CODE_PATTERN, $storedCode);
    }

    public function testPersistingParticipantThatCannotRegenerateTieCodeFailsAfterTenAttempts(): void
    {
        $app = $this->getTestApp();
        $event = $this->getService($app, EventRepository::class)->get(1);

        $existingCode = $this->createParticipantWithRole($app->getContainer(), 'ist', $event)->tieCode;
        $user = $this->getService($app, UserService::class)->registerEmailUser(
            'tie-code-guard-' . bin2hex(random_bytes(6)) . '@example.com',
            $event,
        );

        $colliding = new class () extends Ist {
            public function regenerateTieCode(): void
            {
            }
        };
        $colliding->user = $user;
        $colliding->role = ParticipantRole::Ist;
        $colliding->tieCode = $existingCode;

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Failed to generate a unique tie code after 10 attempts');
        $this->getService($app, IstRepository::class)->persist($colliding);
    }
}
