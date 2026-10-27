<?php

declare(strict_types=1);

namespace Tests\Functional;

use Closure;
use kissj\Event\Event;
use kissj\Event\EventType\Aqua\EventTypeAqua;
use kissj\Event\EventType\Cej\EventTypeCej;
use kissj\Event\EventType\EventType;
use kissj\Event\EventType\EventTypeDefault;
use kissj\Event\EventType\Jj\EventTypeJj;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use kissj\Event\EventType\Miquik\EventTypeMiquik;
use kissj\Event\EventType\Navigamus\EventTypeNavigamus;
use kissj\Event\EventType\Nsj\EventTypeNsj;
use kissj\Event\EventType\Obrok\EventTypeObrok;
use kissj\Event\EventType\Ospz\EventTypeOspz;
use kissj\Event\EventType\Wsj\EventTypeWsj;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\TechnicalTeam\TechnicalTeamLeader;
use kissj\Participant\TechnicalTeam\TechnicalTeamParticipant;
use kissj\Payment\Payment;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use Slim\App;
use Tests\AppTestCase;
use Throwable;

class EventTypeConsistencyTest extends AppTestCase
{
    private const array EVENT_TYPES = [
        'default' => EventTypeDefault::class,
        'aqua' => EventTypeAqua::class,
        'cej' => EventTypeCej::class,
        'korbo' => EventTypeKorbo::class,
        'miquik' => EventTypeMiquik::class,
        'navigamus' => EventTypeNavigamus::class,
        'nsj' => EventTypeNsj::class,
        'wsj' => EventTypeWsj::class,
        'obrok' => EventTypeObrok::class,
        'ospz' => EventTypeOspz::class,
        'jj' => EventTypeJj::class,
    ];

    private const array ROLES = ['ist', 'pl', 'pp', 'guest', 'tl', 'tp', 'ot', 'ttl', 'ttp'];

    private const string PUBLIC_DIR = __DIR__ . '/../../public/';
    private const string TEMPLATE_DIR = __DIR__ . '/../../src/Templates/translatable/';

    public function testEventTypeListCoversEveryConcreteEventType(): void
    {
        $eventTypeDir = __DIR__ . '/../../src/Event/EventType';
        $topLevelFiles = glob($eventTypeDir . '/EventType?*.php');
        $nestedFiles = glob($eventTypeDir . '/*/EventType*.php');
        self::assertIsArray($topLevelFiles);
        self::assertIsArray($nestedFiles);

        self::assertEqualsCanonicalizing(
            array_map(
                static fn (string $file): string => basename($file, '.php'),
                [...$topLevelFiles, ...$nestedFiles],
            ),
            array_map(
                static fn (string $eventTypeClass): string => (new ReflectionClass($eventTypeClass))->getShortName(),
                array_values(self::EVENT_TYPES),
            ),
        );
    }

    public function testRoleListCoversEveryParticipantRole(): void
    {
        self::assertEqualsCanonicalizing(
            array_map(static fn (ParticipantRole $role): string => $role->value, ParticipantRole::cases()),
            self::ROLES,
        );
    }

    /**
     * @return iterable<string, array{string, class-string<EventType>, string, ?string}>
     */
    public static function providePriceCases(): iterable
    {
        foreach (self::EVENT_TYPES as $eventTypeSlug => $eventTypeClass) {
            $contingents = (new $eventTypeClass())->getContingents();
            $checkedContingents = $eventTypeSlug === 'cej' ? $contingents : [$contingents[0] ?? null];
            foreach ($checkedContingents as $contingent) {
                foreach (self::ROLES as $role) {
                    $caseName = sprintf('%s %s %s', $eventTypeSlug, $role, $contingent ?? 'without contingent');

                    yield $caseName => [$eventTypeSlug, $eventTypeClass, $role, $contingent];
                }
            }
        }
    }

    /**
     * @param class-string<EventType> $eventTypeClass
     */
    #[DataProvider('providePriceCases')]
    public function testGetPriceAgreesWithTransformPaymentPrice(
        string $eventTypeSlug,
        string $eventTypeClass,
        string $role,
        ?string $contingent,
    ): void {
        $app = $this->getTestApp();
        $event = $this->getTypedTestEvent($app, $eventTypeSlug, 'consistency-' . $eventTypeSlug);
        $this->mutateEventForTest($app->getContainer(), $event, [
            'defaultPrice' => 1000,
            'guestPrice' => 500,
            'organizingTeamPrice' => 300,
            'technicalTeamPrice' => 200,
        ]);
        $participant = $this->createPricedParticipant($app, $event, $role, $contingent);
        $eventType = $event->getEventType();
        self::assertInstanceOf($eventTypeClass, $eventType);
        $payment = $this->createBlankPayment();

        $price = $this->callCatching(static fn (): int => $eventType->getPrice($participant));
        $paymentPrice = $this->callCatching(
            static fn (): string => $eventType->transformPaymentPrice($payment, $participant)->price,
        );

        $message = sprintf(
            '%s, role %s, contingent %s: getPrice() gave %s, transformPaymentPrice() gave %s',
            $eventTypeSlug,
            $role,
            $contingent ?? 'none',
            $this->describe($price),
            $this->describe($paymentPrice),
        );

        if ($eventTypeSlug !== 'cej' || in_array($role, ['pl', 'ist'], true)) {
            self::assertNotInstanceOf(Throwable::class, $price, $message);
        }

        if ($price instanceof Throwable || $paymentPrice instanceof Throwable) {
            self::assertSame($this->throwableClass($price), $this->throwableClass($paymentPrice), $message);

            return;
        }

        self::assertSame((string)$price, $paymentPrice, $message);
    }

    /**
     * @return iterable<string, array{class-string<EventType>}>
     */
    public static function provideEventTypes(): iterable
    {
        foreach (self::EVENT_TYPES as $eventTypeSlug => $eventTypeClass) {
            yield $eventTypeSlug => [$eventTypeClass];
        }
    }

    /**
     * @param class-string<EventType> $eventTypeClass
     */
    #[DataProvider('provideEventTypes')]
    public function testReferencedFilesExist(string $eventTypeClass): void
    {
        $eventType = new $eventTypeClass();

        $publicFiles = [
            $eventType->getStylesheetNameWithoutLeadingSlash(),
            $eventType->getScriptNameWithoutLeadingSlash(),
            $eventType->getBadgeStylesheetNameWithoutLeadingSlash(),
            $eventType->getEmailStylesheetNameWithoutLeadingSlash(),
            $eventType->getFaviconNameWithoutLeadingSlash(),
            $eventType->getAppleTouchIconNameWithoutLeadingSlash(),
        ];
        foreach (array_filter($publicFiles, static fn (?string $path): bool => $path !== null) as $path) {
            self::assertTrue(
                is_file(self::PUBLIC_DIR . $path),
                $eventTypeClass . ' references missing public/' . $path,
            );
        }

        $templates = [
            $eventType->getCelebrationTemplate(),
            $eventType->getBadgeTemplateName(),
            $eventType->getBlankBadgeTemplateName(),
            $eventType->getRosterTemplateName(),
            $eventType->getReceiptTemplateName(new Participant()),
        ];
        foreach (array_filter($templates, static fn (?string $path): bool => $path !== null) as $path) {
            self::assertTrue(
                is_file(self::TEMPLATE_DIR . $path),
                $eventTypeClass . ' references missing template ' . $path,
            );
        }

        foreach ($eventType->getTranslationFilePaths() as $path) {
            self::assertTrue(is_file($path), $eventTypeClass . ' references missing translation file ' . $path);
        }
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createPricedParticipant(App $app, Event $event, string $role, ?string $contingent): Participant
    {
        $container = $app->getContainer();
        $participantRepository = $this->getService($app, ParticipantRepository::class);

        if ($role === 'pp') {
            $patrolLeader = $this->reloadParticipant(
                $participantRepository,
                $this->createParticipantWithRole($container, 'pl', $event),
            );
            self::assertInstanceOf(PatrolLeader::class, $patrolLeader);
            $participant = new PatrolParticipant();
            $participant->patrolLeader = $patrolLeader;
        } elseif ($role === 'ttp') {
            $technicalTeamLeader = $this->reloadParticipant(
                $participantRepository,
                $this->createParticipantWithRole($container, 'ttl', $event),
            );
            self::assertInstanceOf(TechnicalTeamLeader::class, $technicalTeamLeader);
            $participant = new TechnicalTeamParticipant();
            $participant->technicalTeamLeader = $technicalTeamLeader;
        } else {
            $participant = $this->createParticipantWithRole($container, $role, $event);
        }

        $participant->contingent = $contingent;
        $participantRepository->persist($participant);

        return $this->reloadParticipant($participantRepository, $participant);
    }

    private function reloadParticipant(
        ParticipantRepository $participantRepository,
        Participant $participant,
    ): Participant {
        $reloadedParticipant = $participantRepository->get($participant->id);
        self::assertInstanceOf(Participant::class, $reloadedParticipant);

        return $reloadedParticipant;
    }

    private function createBlankPayment(): Payment
    {
        $payment = new Payment();
        $payment->variableSymbol = '4200000001';
        $payment->note = '';

        return $payment;
    }

    /**
     * @param Closure(): (int|string) $priceCall
     */
    private function callCatching(Closure $priceCall): int|string|Throwable
    {
        try {
            return $priceCall();
        } catch (Throwable $throwable) {
            return $throwable;
        }
    }

    private function describe(int|string|Throwable $result): string
    {
        if ($result instanceof Throwable) {
            return $result::class . ' "' . $result->getMessage() . '"';
        }

        return (string)$result;
    }

    private function throwableClass(int|string|Throwable $result): ?string
    {
        if ($result instanceof Throwable) {
            return $result::class;
        }

        return null;
    }
}
