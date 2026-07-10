<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Deal\DealRepository;
use kissj\Event\EventRepository;
use kissj\Participant\Ist\Ist;
use kissj\Participant\Ist\IstRepository;
use kissj\User\UserService;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Slim\App;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Headers;
use Slim\Psr7\Request;
use Slim\Psr7\Uri;
use Tests\AppTestCase;

class DealTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;
    private const string DEALS_API_KEY = 'test-deals-api-key';
    private const string IST_ROLES_DEAL_SLUG = 'ist-roles';

    private ?ContainerInterface $containerForCleanup = null;

    protected function tearDown(): void
    {
        // the test database is shared, so revert the CEJ event type for following tests
        if ($this->containerForCleanup !== null) {
            $this->resetEventToDefault($this->containerForCleanup);
            $this->containerForCleanup = null;
        }

        parent::tearDown();
    }

    public function testDashboardOffersFillAgainWhenDealIsDone(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        $this->setCejEventType($container);
        $ist = $this->createCejIst($container, 'deal-fill-again-test');

        // first dashboard load creates the deal with the form URL
        $responseBefore = $this->loadDashboardAs($ist);
        self::assertSame(200, $responseBefore->getStatusCode());
        self::assertStringContainsString('docs.google.com/forms', (string)$responseBefore->getBody());

        $webhookResponse = $this->postDealWebhook($ist->tieCode, ['What is your role?' => 'first answer']);
        self::assertSame(201, $webhookResponse->getStatusCode());

        // done deal must still offer the form link so participant can fill it again
        $responseAfter = $this->loadDashboardAs($ist);
        self::assertSame(200, $responseAfter->getStatusCode());
        $body = (string)$responseAfter->getBody();
        self::assertStringContainsString('deal-fill-again', $body);
        self::assertStringContainsString('docs.google.com/forms', $body);
    }

    public function testDashboardShowsDoneTimestampWhenDealIsDone(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        $this->setCejEventType($container);
        $ist = $this->createCejIst($container, 'deal-timestamp-test');

        $this->loadDashboardAs($ist);
        $this->postDealWebhook($ist->tieCode, ['What is your role?' => 'first answer']);

        $istRepository = $this->getIstRepository($container);
        $deal = $istRepository->get($ist->id)->findDeal(self::IST_ROLES_DEAL_SLUG);
        self::assertNotNull($deal);
        self::assertNotNull($deal->doneAt);

        $response = $this->loadDashboardAs($ist);
        self::assertStringContainsString(
            $deal->doneAt->format('d. m. Y'),
            (string)$response->getBody(),
        );
    }

    public function testDashboardHidesFillAgainWhenDealHasNoUrl(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        $this->setCejEventType($container);
        $ist = $this->createCejIst($container, 'deal-no-url-test');

        // admin-created deal has no form URL to offer
        /** @var DealRepository $dealRepository */
        $dealRepository = $container->get(DealRepository::class);
        $dealRepository->setDealAsDone($ist, self::IST_ROLES_DEAL_SLUG);

        $response = $this->loadDashboardAs($ist);
        self::assertSame(200, $response->getStatusCode());
        self::assertStringNotContainsString('deal-fill-again', (string)$response->getBody());
    }

    public function testWebhookResubmissionOverwritesDoneDeal(): void
    {
        $app = $this->getTestApp();
        $container = $this->getContainer($app);
        $this->setCejEventType($container);
        $ist = $this->createCejIst($container, 'deal-resubmit-test');

        $this->loadDashboardAs($ist);

        $firstResponse = $this->postDealWebhook($ist->tieCode, ['What is your role?' => 'first answer']);
        self::assertSame(201, $firstResponse->getStatusCode());

        $istRepository = $this->getIstRepository($container);
        $dealAfterFirst = $istRepository->get($ist->id)->findDeal(self::IST_ROLES_DEAL_SLUG);
        self::assertNotNull($dealAfterFirst);
        self::assertTrue($dealAfterFirst->isDone);
        self::assertStringContainsString('first answer', $dealAfterFirst->data);

        $secondResponse = $this->postDealWebhook($ist->tieCode, ['What is your role?' => 'corrected answer']);
        self::assertSame(201, $secondResponse->getStatusCode());

        $dealAfterSecond = $istRepository->get($ist->id)->findDeal(self::IST_ROLES_DEAL_SLUG);
        self::assertNotNull($dealAfterSecond);
        self::assertTrue($dealAfterSecond->isDone);
        self::assertStringContainsString('corrected answer', $dealAfterSecond->data);
        self::assertStringNotContainsString('first answer', $dealAfterSecond->data);
        self::assertSame($dealAfterFirst->id, $dealAfterSecond->id);
    }

    private function getContainer(App $app): ContainerInterface
    {
        $container = $app->getContainer();
        if ($container === null) {
            throw new RuntimeException('Container is null');
        }

        return $container;
    }

    private function setCejEventType(ContainerInterface $container): void
    {
        $this->containerForCleanup = $container;

        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $connection->query(
            'UPDATE event SET event_type = %s WHERE slug = %s',
            'cej',
            self::TEST_EVENT_SLUG,
        );

        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }
        $event->apiKeyDeals = self::DEALS_API_KEY;
        $eventRepository->persist($event);
    }

    private function createCejIst(ContainerInterface $container, string $emailPrefix): Ist
    {
        /** @var UserService $userService */
        $userService = $container->get(UserService::class);
        /** @var EventRepository $eventRepository */
        $eventRepository = $container->get(EventRepository::class);

        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            throw new RuntimeException('Test event not found');
        }

        // the test database is shared, so email must be unique across runs
        $email = $emailPrefix . '-' . bin2hex(random_bytes(4)) . '@example.com';
        $user = $userService->registerEmailUser($email, $event);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getIstRepository($container);
        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Deal';
        $ist->lastName = 'Tester';
        $ist->email = $email;
        $istRepository->persist($ist);

        return $istRepository->get($ist->id);
    }

    private function getIstRepository(ContainerInterface $container): IstRepository
    {
        /** @var IstRepository $istRepository */
        $istRepository = $container->get(IstRepository::class);

        return $istRepository;
    }

    private function loadDashboardAs(Ist $ist): \Psr\Http\Message\ResponseInterface
    {
        $_SESSION['user']['id'] = $ist->getUserButNotNull()->id;
        session_write_close();

        $app = $this->getTestApp(false);

        return $app->handle($this->createRequest(self::BASE_URL . '/participant/dashboard'));
    }

    /**
     * @param array<string, string> $answers
     */
    private function postDealWebhook(string $tieCode, array $answers): \Psr\Http\Message\ResponseInterface
    {
        $payload = array_merge($answers, [
            'TIE code' => $tieCode,
            'slug' => self::IST_ROLES_DEAL_SLUG,
        ]);

        $uri = new Uri('', '', 80, '/v3/deal');
        $handle = fopen('php://temp', 'wb+');
        if ($handle === false) {
            throw new RuntimeException('opening php://temp failed');
        }
        fwrite($handle, json_encode($payload, JSON_THROW_ON_ERROR));
        rewind($handle);

        $stream = (new StreamFactory())->createStreamFromResource($handle);
        $headers = new Headers([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . self::DEALS_API_KEY,
        ]);
        $request = new Request('POST', $uri, $headers, [], [], $stream);

        return $this->getTestApp(false)->handle($request);
    }
}
