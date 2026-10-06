<?php

declare(strict_types=1);

namespace Tests\Functional;

use ArrayObject;
use Closure;
use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Mailer\Mailer;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Participant\TshirtService;
use kissj\Payment\PaymentService;
use kissj\Telemetry\Metrics;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use LeanMapper\Connection;
use LeanMapper\IEntityFactory;
use LeanMapper\IMapper;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Slim\App;
use Tests\AppTestCase;

class ParticipantCloseRegistrationRaceTest extends AppTestCase
{
    public function testRaceLoserIsNotClosedWhenCompetitorTookLastPlace(): void
    {
        $app = $this->getTestApp();
        [$ourIst, $otherIst, $recipients] = $this->prepare($app);

        $service = $this->buildService($app, fn () => $this->closeUser($app, $otherIst));
        $result = $service->closeRegistration($ourIst);

        self::assertFalse($result->isValid);
        self::assertSame('flash.warning.fullRegistration', $result->warnings[0]['key']);
        $reloaded = $this->getService($app, IstRepository::class)->get($ourIst->id);
        self::assertSame(UserStatus::Open, $reloaded->getUserButNotNull()->status);
        self::assertNull($reloaded->registrationCloseDate);
        self::assertNotContains($ourIst->email, $recipients->getArrayCopy());
    }

    public function testDoubleSubmitClosesOnceAndMailsOnce(): void
    {
        $app = $this->getTestApp();
        [$ourIst, , $recipients] = $this->prepare($app);

        $service = $this->buildService($app, fn () => $this->closeUser($app, $ourIst));
        $result = $service->closeRegistration($ourIst);

        self::assertTrue($result->isValid);
        self::assertSame([], $result->warnings);
        $reloaded = $this->getService($app, IstRepository::class)->get($ourIst->id);
        self::assertSame(UserStatus::Closed, $reloaded->getUserButNotNull()->status);
        self::assertNotContains($ourIst->email, $recipients->getArrayCopy());
        self::assertNull($reloaded->registrationCloseDate);
    }

    public function testAlreadyClosedUserIsNotShownFullByOwnPlace(): void
    {
        $app = $this->getTestApp();
        [$ourIst, , $recipients] = $this->prepare($app);
        $this->closeUser($app, $ourIst);

        $service = $this->buildService($app, static function (): void {
        });
        $result = $service->closeRegistration($ourIst);

        self::assertTrue($result->isValid);
        self::assertSame([], $result->warnings);
        self::assertNull($this->getService($app, IstRepository::class)->get($ourIst->id)->registrationCloseDate);
        self::assertNotContains($ourIst->email, $recipients->getArrayCopy());
    }

    public function testUncontendedCloseStillClosesAndMails(): void
    {
        $app = $this->getTestApp();
        [$ourIst, , $recipients] = $this->prepare($app);

        $service = $this->buildService($app, static function (): void {
        });
        $result = $service->closeRegistration($ourIst);

        self::assertTrue($result->isValid);
        $reloaded = $this->getService($app, IstRepository::class)->get($ourIst->id);
        self::assertSame(UserStatus::Closed, $reloaded->getUserButNotNull()->status);
        self::assertNotNull($reloaded->registrationCloseDate);
        self::assertCount(1, array_keys($recipients->getArrayCopy(), $ourIst->email, true));
    }

    /**
     * @param App<ContainerInterface> $app
     * @return array{Ist, Ist, ArrayObject<int, string>}
     */
    private function prepare(App $app): array
    {
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }
        $this->mutateEventForTest($app->getContainer(), $event, [
            'maximalClosedIstsCount' => 1,
            'startRegistration' => DateTimeUtils::getDateTime('-1 day'),
        ]);

        $ourIst = $this->createFilledOpenIst($app, $event);
        $otherIst = $this->createFilledOpenIst($app, $event);

        $service = $this->getService($app, ParticipantService::class);
        self::assertTrue($service->isCloseRegistrationValid($ourIst)->isValid);
        self::assertTrue($service->isCloseRegistrationValid($otherIst)->isValid);

        $this->initializeMailerSettings($app, $event);
        $recipients = $this->captureSentMessageRecipients($app);

        return [$ourIst, $otherIst, $recipients];
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createFilledOpenIst(App $app, Event $event): Ist
    {
        $email = 'race-' . bin2hex(random_bytes(6)) . '@example.com';
        $userService = $this->getService($app, UserService::class);
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Race';
        $ist->lastName = 'Tester';
        $ist->nickname = 'Racer';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->permanentResidence = '1 Scout Street, Scout Town';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);

        return $ist;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function closeUser(App $app, Ist $ist): void
    {
        $userRepository = $this->getService($app, UserRepository::class);
        $user = $userRepository->get($ist->getUserButNotNull()->id);
        $user->status = UserStatus::Closed;
        $userRepository->persist($user);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function buildService(App $app, Closure $hook): ParticipantService
    {
        $eventRepository = new class (
            $this->getService($app, Connection::class),
            $this->getService($app, IMapper::class),
            $this->getService($app, IEntityFactory::class),
            $hook,
        ) extends EventRepository {
            public function __construct(
                Connection $connection,
                IMapper $mapper,
                IEntityFactory $entityFactory,
                private readonly Closure $hook,
            ) {
                parent::__construct($connection, $mapper, $entityFactory);
            }

            protected function getTable(): string
            {
                return 'event';
            }

            public function lockForCapacity(Event $event): void
            {
                ($this->hook)();
                parent::lockForCapacity($event);
            }
        };

        return new ParticipantService(
            $this->getService($app, ParticipantRepository::class),
            $this->getService($app, TroopParticipantRepository::class),
            $this->getService($app, PaymentService::class),
            $this->getService($app, UserService::class),
            $this->getService($app, Mailer::class),
            $this->getService($app, Metrics::class),
            $this->getService($app, TshirtService::class),
            $eventRepository,
            $this->getService($app, UserRepository::class),
        );
    }
}
