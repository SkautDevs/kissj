<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Participant\Participant;
use kissj\Programme\Device\DevicePairingAttempt;
use kissj\Programme\Device\DevicePairingAttemptRepository;
use LogicException;
use RuntimeException;
use Tests\AppTestCase;

class RepositoryTransactionalTest extends AppTestCase
{
    public function testNestedCallJoinsOuterTransaction(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DevicePairingAttemptRepository::class);
        $participant = $this->createParticipantWithRole(
            $app->getContainer() ?? throw new LogicException('container missing'),
            'ist',
        );

        try {
            $repository->transactional(function () use ($repository, $participant): void {
                $repository->transactional(fn () => $this->createAttempt($repository, $participant));

                throw new RuntimeException('outer fails after inner finished');
            });
        } catch (RuntimeException $e) {
            self::assertSame('outer fails after inner finished', $e->getMessage());
        }

        self::assertSame(0, $repository->countBy(['participant' => $participant]));
    }

    public function testCallbackResultIsReturnedAndCommitted(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DevicePairingAttemptRepository::class);
        $participant = $this->createParticipantWithRole(
            $app->getContainer() ?? throw new LogicException('container missing'),
            'ist',
        );

        $result = $repository->transactional(function () use ($repository, $participant): string {
            $this->createAttempt($repository, $participant);

            return 'done';
        });

        self::assertSame('done', $result);
        self::assertSame(1, $repository->countBy(['participant' => $participant]));
    }

    public function testRowLockOutsideTransactionIsRefused(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DevicePairingAttemptRepository::class);
        $attempt = $this->createAttempt(
            $repository,
            $this->createParticipantWithRole(
                $app->getContainer() ?? throw new LogicException('container missing'),
                'ist',
            ),
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Row lock outside a transaction would be released right away');
        $repository->findWithLock($attempt->id);
    }

    public function testRowLockInsideTransactionReturnsRow(): void
    {
        $app = $this->getTestApp();
        $repository = $this->getService($app, DevicePairingAttemptRepository::class);
        $attempt = $this->createAttempt(
            $repository,
            $this->createParticipantWithRole(
                $app->getContainer() ?? throw new LogicException('container missing'),
                'ist',
            ),
        );

        $locked = $repository->transactional(fn () => $repository->findWithLock($attempt->id));

        self::assertInstanceOf(DevicePairingAttempt::class, $locked);
        self::assertSame($attempt->id, $locked->id);
    }

    private function createAttempt(
        DevicePairingAttemptRepository $repository,
        Participant $participant,
    ): DevicePairingAttempt {
        $attempt = new DevicePairingAttempt();
        $attempt->participant = $participant;
        $repository->persist($attempt);

        return $attempt;
    }
}
