<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Mailer\Mailer;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
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

        self::assertStringContainsString('těšíme se na tebe v servis týmu', $html);
        self::assertStringContainsString('kód pro přednostní přihlášení patrol', $html);
        self::assertStringContainsString('<strong>Co bude dál?</strong>', $html);
        self::assertStringNotContainsString('Děkujeme, že ses přihlásil na Obrok', $html);
        self::assertStringNotContainsString('email.payment-successful-ist', $html);
    }

    public function testObrokTroopLeaderMailKeepsObrokText(): void
    {
        $app = $this->getTestApp();
        $html = $this->sendPaidMailForObrokParticipant($app, 'tl');

        self::assertStringContainsString('Děkujeme, že ses přihlásil na Obrok', $html);
        self::assertStringNotContainsString('servis týmu', $html);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function sendPaidMailForObrokParticipant(App $app, string $role): string
    {
        $container = $app->getContainer();
        $this->containerForCleanup = $container;
        $this->setEventType($container, 'obrok', 'test-event-slug');

        $event = $this->getService($app, EventRepository::class)->findBySlug('test-event-slug');
        self::assertInstanceOf(Event::class, $event);
        $this->initializeMailerSettings($app, $event);

        $app->handle($this->createRequest('/v2/event/test-event-slug'));

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
