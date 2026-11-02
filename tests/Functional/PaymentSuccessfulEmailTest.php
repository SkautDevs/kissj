<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Mailer\Mailer;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Translation\CurrentTranslator;
use Psr\Container\ContainerInterface;
use Slim\App;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\EventListener\MessageLoggerListener;
use Tests\AppTestCase;

class PaymentSuccessfulEmailTest extends AppTestCase
{
    private ?ContainerInterface $containerForCleanup = null;

    protected function tearDown(): void
    {
        if ($this->containerForCleanup !== null) {
            $this->resetEventToDefault($this->containerForCleanup);
            $this->containerForCleanup = null;
        }

        parent::tearDown();
    }

    public function testObrokIstMailCarriesIstText(): void
    {
        $app = $this->getTestApp();
        $html = $this->sendPaidMailForObrokParticipant($app, 'ist');

        self::assertStringContainsString('<strong>Co bude dál?</strong>', $html);
        self::assertStringContainsString(
            'těšíme se na tebe v servis týmu! Ve své registraci najdeš i kód pro přednostní přihlášení patrol.',
            $html,
        );
        self::assertStringContainsString(
            '<p>Ulož si tenhle QR kód, ať ho máš po ruce, protože ho budeš potřebovat při vstupu na akci:</p>',
            $html,
        );
        self::assertStringContainsString('<a href="mailto:servis@obrok.skaut.cz">servis@obrok.skaut.cz</a>', $html);
        self::assertStringContainsString('Do té doby si užij čekání na akci', $html);
        self::assertStringNotContainsString('Děkujeme, že ses přihlásil na Obrok', $html);
        self::assertStringNotContainsString('email.payment-successful-ist', $html);
    }

    public function testObrokIstMailInEnglish(): void
    {
        $app = $this->getTestApp();
        $html = $this->sendPaidMailForObrokParticipant($app, 'ist', 'en');

        self::assertStringContainsString('<a href="mailto:servis@obrok.skaut.cz">servis@obrok.skaut.cz</a>', $html);
        self::assertStringContainsString('<strong>What happens next?</strong>', $html);
        self::assertStringContainsString(
            'In your registration you will also find the code for priority registration of patrols.',
            $html,
        );
        self::assertStringNotContainsString('servis týmu', $html);
        self::assertStringNotContainsString('email.payment-successful-ist', $html);
        self::assertStringContainsString('If you have any questions, you may seek the answer at our webpage', $html);
        self::assertStringNotContainsString('mohl bys', $html);
    }

    public function testObrokTroopLeaderMailKeepsObrokText(): void
    {
        $app = $this->getTestApp();
        $html = $this->sendPaidMailForObrokParticipant($app, 'tl');

        self::assertStringContainsString('Děkujeme, že ses přihlásil na Obrok', $html);
        self::assertStringNotContainsString('servis týmu', $html);
    }

    public function testObrokTroopLeaderMailInEnglish(): void
    {
        $app = $this->getTestApp();
        $html = $this->sendPaidMailForObrokParticipant($app, 'tl', 'en');

        self::assertStringContainsString('Thank you for registering for Obrok', $html);
        self::assertStringNotContainsString('Děkujeme, že ses přihlásil na Obrok', $html);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function sendPaidMailForObrokParticipant(App $app, string $role, ?string $locale = null): string
    {
        $container = $app->getContainer();
        $this->containerForCleanup = $container;
        $this->setEventType($container, 'obrok', 'test-event-slug');

        $event = $this->getService($app, EventRepository::class)->findBySlug('test-event-slug');
        self::assertInstanceOf(Event::class, $event);
        $this->initializeMailerSettings($app, $event);

        $app->handle($this->createRequest('/v2/event/test-event-slug'));
        if ($locale !== null) {
            $this->getService($app, CurrentTranslator::class)->setLocale($locale);
        }

        $created = $this->createParticipantWithRole($container, $role, $event);
        $created->gender = 'man';
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $participantRepository->persist($created);
        $participant = $participantRepository->getParticipantFromUser($created->getUserButNotNull());

        return $this->captureSentPaidMailHtml($app, $participant);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function captureSentPaidMailHtml(App $app, Participant $participant): string
    {
        $dispatcher = $this->getService($app, EventDispatcherInterface::class);
        $messageLogger = new MessageLoggerListener();
        $dispatcher->addSubscriber($messageLogger);

        $this->getService($app, Mailer::class)->sendRegistrationPaid($participant);

        $events = $messageLogger->getEvents()->getEvents();
        self::assertCount(1, $events);
        $message = $events[0]->getMessage();
        self::assertInstanceOf(TemplatedEmail::class, $message);

        $htmlBody = $message->getHtmlBody();
        self::assertIsString($htmlBody);

        return $htmlBody;
    }
}
