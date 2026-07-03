<?php

declare(strict_types=1);

namespace Tests\Unit\Orm;

use kissj\BankPayment\BankPayment;
use kissj\Deal\Deal;
use kissj\Event\Event;
use kissj\Orm\Mapper;
use kissj\Participant\Guest\Guest;
use kissj\Participant\Ist\Ist;
use kissj\Participant\OrganizingTeam\OrganizingTeam;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Payment\Payment;
use kissj\User\LoginToken;
use kissj\User\User;
use LeanMapper\Exception\InvalidStateException;
use LeanMapper\Row;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class MapperTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Mapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new Mapper();
    }

    /**
     * @return array<string, array{class-string, string}>
     */
    public static function provideParticipantVariants(): array
    {
        return [
            'PatrolLeader' => [PatrolLeader::class, 'participant'],
            'PatrolParticipant' => [PatrolParticipant::class, 'participant'],
            'TroopLeader' => [TroopLeader::class, 'participant'],
            'TroopParticipant' => [TroopParticipant::class, 'participant'],
            'Ist' => [Ist::class, 'participant'],
            'Guest' => [Guest::class, 'participant'],
            'OrganizingTeam' => [OrganizingTeam::class, 'participant'],
        ];
    }

    /**
     * @param class-string $entityClass
     */
    #[DataProvider('provideParticipantVariants')]
    public function testAllParticipantVariantsMapToParticipantTable(string $entityClass, string $expectedTable): void
    {
        self::assertSame($expectedTable, $this->mapper->getTable($entityClass));
    }

    public function testNonParticipantEntitiesMapToTheirOwnTable(): void
    {
        self::assertSame('user', $this->mapper->getTable(User::class));
        self::assertSame('payment', $this->mapper->getTable(Payment::class));
        self::assertSame('event', $this->mapper->getTable(Event::class));
        self::assertSame('participant', $this->mapper->getTable(Participant::class));
    }

    /**
     * @return array<string, array{ParticipantRole, class-string}>
     */
    public static function provideRoles(): array
    {
        return [
            'pl' => [ParticipantRole::PatrolLeader, PatrolLeader::class],
            'pp' => [ParticipantRole::PatrolParticipant, PatrolParticipant::class],
            'tl' => [ParticipantRole::TroopLeader, TroopLeader::class],
            'tp' => [ParticipantRole::TroopParticipant, TroopParticipant::class],
            'ist' => [ParticipantRole::Ist, Ist::class],
            'guest' => [ParticipantRole::Guest, Guest::class],
            'ot' => [ParticipantRole::OrganizingTeam, OrganizingTeam::class],
        ];
    }

    /**
     * @param class-string $expectedClass
     */
    #[DataProvider('provideRoles')]
    public function testParticipantRowRoutesToRoleEntityClass(ParticipantRole $role, string $expectedClass): void
    {
        $row = Mockery::mock(Row::class);
        $row->shouldReceive('getData')->andReturn(['role' => $role->value, 'id' => 1]);

        self::assertSame($expectedClass, $this->mapper->getEntityClass('participant', $row));
    }

    public function testParticipantTableWithoutRowRoutesToBaseParticipant(): void
    {
        self::assertSame(Participant::class, $this->mapper->getEntityClass('participant', null));
    }

    public function testNonParticipantTablesRouteToTheirEntity(): void
    {
        self::assertSame(User::class, $this->mapper->getEntityClass('user'));
        self::assertSame(LoginToken::class, $this->mapper->getEntityClass('logintoken'));
        self::assertSame(Payment::class, $this->mapper->getEntityClass('payment'));
        self::assertSame(BankPayment::class, $this->mapper->getEntityClass('bankpayment'));
        self::assertSame(Event::class, $this->mapper->getEntityClass('event'));
        self::assertSame(Deal::class, $this->mapper->getEntityClass('deal'));
    }

    public function testUnknownRoleThrows(): void
    {
        $row = Mockery::mock(Row::class);
        $row->shouldReceive('getData')->andReturn(['role' => 'wizard', 'id' => 42]);

        $this->expectException(UnexpectedValueException::class);
        $this->mapper->getEntityClass('participant', $row);
    }

    public function testUnknownTableThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->mapper->getEntityClass('definitely_not_a_table');
    }

    public function testColumnAndFieldRoundTrip(): void
    {
        self::assertSame('variable_symbol', $this->mapper->getColumn(Participant::class, 'variableSymbol'));
        self::assertSame('variableSymbol', $this->mapper->getEntityField('participant', 'variable_symbol'));
    }

    public function testPrimaryKeyIsAlwaysId(): void
    {
        self::assertSame('id', $this->mapper->getPrimaryKey('participant'));
        self::assertSame('id', $this->mapper->getPrimaryKey('event'));
    }

    public function testTableDerivedFromRepositoryClass(): void
    {
        self::assertSame('participant', $this->mapper->getTableByRepositoryClass(
            'kissj\\Participant\\ParticipantRepository',
        ));
    }

    public function testTableFromMalformedRepositoryClassThrows(): void
    {
        $this->expectException(InvalidStateException::class);
        $this->mapper->getTableByRepositoryClass('NotAValidClassName');
    }
}
