<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\Event;
use kissj\Event\EventRepository;
use kissj\Event\EventType\Korbo\EventTypeKorbo;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class PaymentTransferedToYouEmailTest extends AppTestCase
{
    private const string BASE_ENJOY_TEXT = 'Nyní si můžeš plně užít čekání na nadcházející akci! '
        . 'Taky si nic neudělej, ujisti se že tvůj stan je připraven a očekávej další informace. ;)';

    private ?ContainerInterface $containerForCleanup = null;

    protected function tearDown(): void
    {
        // event type mutation below is per-test-database anyway, but stay self-contained like DealTest
        if ($this->containerForCleanup !== null) {
            $this->resetEventToDefault($this->containerForCleanup);
            $this->containerForCleanup = null;
        }

        parent::tearDown();
    }

    public function testBodyExplainsTheTransferAndCarriesTheEventWelcomeText(): void
    {
        $app = $this->getTestApp();
        $twig = $this->getService($app, Twig::class);
        $translator = $this->getService($app, TranslatorInterface::class);
        $event = $this->getSmallTestEvent($this->getService($app, EventRepository::class));

        // '' suffix makes transGendered fall back to the plain key, as the layout and this template share it
        $html = $twig->fetch('emails/payment-transfered-to-you.twig', ['event' => $event, 'genderSuffix' => '']);

        self::assertStringContainsString(
            $translator->trans('email.payment-transfered-to-you.paidRegistrationReceived'),
            $html,
        );
        self::assertStringContainsString(
            $translator->trans('email.payment-successful.enjoy'),
            $html,
        );
    }

    public function testKorboWelcomeTextReachesTheTransferMailThroughEventScope(): void
    {
        $app = $this->getTestApp();
        $container = $app->getContainer();
        $this->containerForCleanup = $container;

        $this->setEventType($container, 'korbo', 'test-event-slug');

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug('test-event-slug');
        self::assertInstanceOf(Event::class, $event);
        self::assertInstanceOf(EventTypeKorbo::class, $event->getEventType());

        $twig = $this->getService($app, Twig::class);

        // hits the plain landing route rather than /login: Korbo enables Skautis login and
        // the fixture event has no skautisAppId, so /login crashes on unrelated setup, not on
        // anything this test cares about. Korbo is cs-only, so the resolved locale is 'cs'
        // with no Accept-Language header on the test request
        $app->handle($this->createRequest('/v2/event/test-event-slug'));

        $korboHtml = $twig->fetch('emails/payment-transfered-to-you.twig', ['event' => $event, 'genderSuffix' => '']);

        self::assertStringContainsString('Milý Korbáčku', $korboHtml);
        self::assertStringNotContainsString(self::BASE_ENJOY_TEXT, $korboHtml);

        // revert half, mirroring TranslatorScopingTest: a non-event request resets the scope
        $app->handle($this->createRequest('/'));

        $baseHtml = $twig->fetch('emails/payment-transfered-to-you.twig', ['event' => $event, 'genderSuffix' => '']);

        self::assertStringNotContainsString('Milý Korbáčku', $baseHtml);
        self::assertStringContainsString(self::BASE_ENJOY_TEXT, $baseHtml);
    }
}
