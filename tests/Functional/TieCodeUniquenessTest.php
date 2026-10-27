<?php

declare(strict_types=1);

namespace Tests\Functional;

use Dibi\UniqueConstraintViolationException;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class TieCodeUniquenessTest extends AppTestCase
{
    private const int TIE_CODE_MIGRATION_VERSION = 20261018000000;

    public function testDuplicateTieCodeIsRejectedByDatabase(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        $first = $this->createOpenIst($container, 'Ann', 'First' . bin2hex(random_bytes(4)));
        $second = $this->createOpenIst($container, 'Bob', 'Second' . bin2hex(random_bytes(4)));

        $second->tieCode = $first->tieCode;

        $this->expectException(UniqueConstraintViolationException::class);
        $participantRepository->persist($second);
    }

    public function testMigrationRepairsLegacyAndDuplicateCodesKeepingTroopLeaders(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $connection = $this->getService($app, Connection::class);

        // un-apply only this migration so the test does not depend on later migrations being reversible
        $connection->query('DELETE FROM phinx_log WHERE version = %i', self::TIE_CODE_MIGRATION_VERSION);
        $connection->query('DROP INDEX participant_tie_code_uindex');

        $keptTl = $this->createParticipantWithTieCode($container, 'tl', 'AAAAAA');
        $duplicateIst = $this->createParticipantWithTieCode($container, 'ist', 'AAAAAA');
        $duplicateGuest = $this->createParticipantWithTieCode($container, 'guest', 'AAAAAA');
        $olderIst = $this->createParticipantWithTieCode($container, 'ist', 'BBBBBB');
        $youngerTl = $this->createParticipantWithTieCode($container, 'tl', 'BBBBBB');
        $olderTl = $this->createParticipantWithTieCode($container, 'tl', 'CCCCCC');
        $newestTl = $this->createParticipantWithTieCode($container, 'tl', 'CCCCCC');
        $leaderlessIst = $this->createParticipantWithTieCode($container, 'ist', 'DDDDDD');
        $newestGuest = $this->createParticipantWithTieCode($container, 'guest', 'DDDDDD');
        $legacyTl = $this->createParticipantWithTieCode($container, 'tl', '123456');
        $legacyIst = $this->createParticipantWithTieCode($container, 'ist', '123456');
        $untouchedIst = $this->createParticipantWithTieCode($container, 'ist', 'EEEEEE');

        $migrateOutput = $this->runPhinx(['command' => 'migrate']);

        // LeanMapper does not identity-map, so the in-memory entities still carry the pre-migration codes
        $codeOf = static fn (Participant $participant): string => $participantRepository
            ->getOneBy(['id' => $participant->id])
            ->tieCode;

        // the troop leader of a group survives whatever its id is
        self::assertSame('AAAAAA', $codeOf($keptTl));
        self::assertSame('BBBBBB', $codeOf($youngerTl));
        // two troop leaders in one group: the newest one survives
        self::assertSame('CCCCCC', $codeOf($newestTl));
        // no troop leader in the group: the newest row survives
        self::assertSame('DDDDDD', $codeOf($newestGuest));
        self::assertSame('EEEEEE', $codeOf($untouchedIst));

        $rewritten = [
            $codeOf($duplicateIst),
            $codeOf($duplicateGuest),
            $codeOf($olderIst),
            $codeOf($olderTl),
            $codeOf($leaderlessIst),
            $codeOf($legacyTl),
            $codeOf($legacyIst),
        ];
        foreach ($rewritten as $code) {
            self::assertMatchesRegularExpression(self::TIE_CODE_PATTERN, $code);
            self::assertNotContains($code, ['AAAAAA', 'BBBBBB', 'CCCCCC', 'DDDDDD', 'EEEEEE', '123456']);
        }
        self::assertCount(12, array_unique([...$rewritten, 'AAAAAA', 'BBBBBB', 'CCCCCC', 'DDDDDD', 'EEEEEE']));

        self::assertSame(7, substr_count($migrateOutput, 'rewrote tie code of participant'));
        self::assertStringContainsString(' AAAAAA -> ', $migrateOutput);
        self::assertStringContainsString(' 123456 -> ', $migrateOutput);
        self::assertStringContainsString(': legacy default', $migrateOutput);
        self::assertStringContainsString(': duplicate of participant ' . $newestTl->id . PHP_EOL, $migrateOutput);
        self::assertStringContainsString(
            'rewrote 2 legacy and 5 duplicate tie codes (2 troop leaders)',
            $migrateOutput,
        );

        // the index is back after the repair
        $duplicateIst = $participantRepository->getOneBy(['id' => $duplicateIst->id]);
        $duplicateIst->tieCode = 'EEEEEE';
        $this->expectException(UniqueConstraintViolationException::class);
        $participantRepository->persist($duplicateIst);
    }

    private function createParticipantWithTieCode(
        ContainerInterface $container,
        string $role,
        string $tieCode,
    ): Participant {
        /** @var ParticipantRepository $participantRepository */
        $participantRepository = $container->get(ParticipantRepository::class);

        $participant = $this->createParticipantWithRole($container, $role);
        $participant->tieCode = $tieCode;
        $participantRepository->persist($participant);

        return $participant;
    }
}
