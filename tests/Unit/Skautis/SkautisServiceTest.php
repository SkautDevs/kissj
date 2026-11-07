<?php

declare(strict_types=1);

namespace Tests\Unit\Skautis;

use DateTimeInterface;
use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Skautis\SkautisFactory;
use kissj\Skautis\SkautisService;
use kissj\User\UserRegeneration;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Skautis\Config;
use Skautis\SessionAdapter\SessionAdapter;
use Skautis\Skautis;
use Skautis\User;
use Skautis\Wsdl\AuthenticationException;
use Skautis\Wsdl\WebServiceFactoryInterface;
use Skautis\Wsdl\WebServiceInterface;
use Skautis\Wsdl\WsdlException;
use Skautis\Wsdl\WsdlManager;

class SkautisServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private LoggerInterface&MockInterface $logger;
    private FlashMessagesInterface&MockInterface $flashMessages;
    private ParticipantRepository&MockInterface $participantRepository;
    private SkautisService $service;

    protected function setUp(): void
    {
        // the library's SessionAdapter reads the superglobal directly
        $_SESSION = [];
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->flashMessages = Mockery::mock(FlashMessagesInterface::class);
        $this->participantRepository = Mockery::mock(ParticipantRepository::class);
        $this->service = $this->createServiceWithWebServiceFactory(Mockery::mock(WebServiceFactoryInterface::class));
        $this->service->initSkautis('test-app-id');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $_SESSION = [];
    }

    public function testMalformedLogoutDateIsRejected(): void
    {
        $this->logger->shouldReceive('warning')
            ->once()
            ->with(
                'Failed to read Skautis login data',
                Mockery::on(
                    fn (array $context) => is_string($context['reason'] ?? null)
                        && str_contains($context['reason'], '2026-09-08T00:00:00'),
                ),
            );

        $accepted = $this->service->saveDataFromPost([
            'skautIS_Token' => 'deadbeef',
            'skautIS_IDRole' => '1',
            'skautIS_IDUnit' => '1',
            'skautIS_DateLogout' => '2026-09-08T00:00:00',
        ]);

        self::assertFalse($accepted);
    }

    public function testWellFormedPostIsAccepted(): void
    {
        $this->logger->shouldNotReceive('warning');

        $accepted = $this->service->saveDataFromPost([
            'skautIS_Token' => 'deadbeef',
            'skautIS_IDRole' => '1',
            'skautIS_IDUnit' => '1',
            'skautIS_DateLogout' => '8. 9. 2026 12:00:00',
        ]);

        self::assertTrue($accepted);
        self::assertSame('deadbeef', $this->storedLoginData()['ID_Login']);
    }

    public function testArrayValuedFieldIsIgnored(): void
    {
        $this->logger->shouldNotReceive('warning');

        $accepted = $this->service->saveDataFromPost([
            'skautIS_Token' => 'deadbeef',
            'skautIS_DateLogout' => ['x'],
        ]);

        self::assertTrue($accepted);
        $loginData = $this->storedLoginData();
        self::assertSame('deadbeef', $loginData['ID_Login']);
        self::assertArrayNotHasKey('LOGOUT_Date', $loginData);
    }

    public function testSuccessfulRefreshStoresNewLogoutDate(): void
    {
        $webService = Mockery::mock(WebServiceInterface::class);
        $webService->expects('LoginUpdateRefresh')
            ->with(['ID' => 'deadbeef'])
            ->andReturn(new class () {
                public string $DateLogout = '2026-09-08T13:30:00.123';
            });
        $service = $this->createServiceWithLogin($webService);
        $this->logger->shouldNotReceive('info', 'warning', 'error');

        $service->initSkautis('test-app-id');

        $logoutDate = $this->storedLoginData()['LOGOUT_Date'];
        self::assertInstanceOf(DateTimeInterface::class, $logoutDate);
        self::assertSame('2026-09-08T13:30:00+02:00', $logoutDate->format(DATE_ATOM));
    }

    public function testLoggedOutSkautisLoginIsResetOnRefresh(): void
    {
        $logout = new AuthenticationException('Uživatel byl odhlášen');
        $service = $this->createServiceWithLoginAndRefreshFailure($logout);
        $this->logger->expects('info')->with('Skautis login expired, login data reset', ['exception' => $logout]);
        $this->logger->shouldNotReceive('warning');

        $service->initSkautis('test-app-id');

        self::assertFalse($service->isUserLoggedIn());
        self::assertSame([], $this->storedLoginData());

        $service->initSkautis('test-app-id');
    }

    public function testOtherSkautisRefreshFailureKeepsLoginAndWarns(): void
    {
        $failure = new WsdlException('boom');
        $service = $this->createServiceWithLoginAndRefreshFailure($failure);
        $this->logger->expects('warning')->with('Failed to refresh Skautis login', ['exception' => $failure]);
        $this->logger->shouldNotReceive('info');

        $service->initSkautis('test-app-id');

        self::assertSame('deadbeef', $this->storedLoginData()['ID_Login']);
    }

    public function testNoRefreshWithoutLoginId(): void
    {
        $webServiceFactory = Mockery::mock(WebServiceFactoryInterface::class);
        $webServiceFactory->shouldNotReceive('createWebService');
        $service = $this->createServiceWithWebServiceFactory($webServiceFactory);
        $this->logger->shouldNotReceive('info', 'warning', 'error');

        $service->initSkautis('test-app-id');

        self::assertFalse($service->isUserLoggedIn());
    }

    public function testPrefillWithoutSkautisLoginFlashesNotLoggedIn(): void
    {
        $webServiceFactory = Mockery::mock(WebServiceFactoryInterface::class);
        $webServiceFactory->shouldNotReceive('createWebService');
        $service = $this->createServiceWithWebServiceFactory($webServiceFactory);
        $service->initSkautis('test-app-id');
        $participant = self::createStub(Participant::class);
        $this->flashMessages->expects('error')->with('flash.error.skautisUserNotLoggedIn');
        $this->participantRepository->shouldNotReceive('persist');
        $this->logger->shouldNotReceive('info', 'warning', 'error');

        self::assertSame($participant, $service->prefillDataFromSkautis($participant));
    }

    private function createServiceWithLoginAndRefreshFailure(WsdlException $refreshFailure): SkautisService
    {
        $webService = Mockery::mock(WebServiceInterface::class);
        $webService->expects('LoginUpdateRefresh')->with(['ID' => 'deadbeef'])->andThrow($refreshFailure);

        return $this->createServiceWithLogin($webService);
    }

    private function createServiceWithLogin(WebServiceInterface $webService): SkautisService
    {
        $webServiceFactory = Mockery::mock(WebServiceFactoryInterface::class);
        $webServiceFactory->allows('createWebService')->andReturn($webService);
        $service = $this->createServiceWithWebServiceFactory($webServiceFactory);
        $service->initSkautis('test-app-id');
        $service->saveDataFromPost([
            'skautIS_Token' => 'deadbeef',
            'skautIS_IDRole' => '1',
            'skautIS_IDUnit' => '1',
            'skautIS_DateLogout' => '8. 9. 2026 12:00:00',
        ]);

        return $service;
    }

    private function createServiceWithWebServiceFactory(WebServiceFactoryInterface $webServiceFactory): SkautisService
    {
        $wsdlManager = new WsdlManager($webServiceFactory, new Config('test-app-id', true, true, true));
        $skautisFactory = self::createStub(SkautisFactory::class);
        $skautisFactory->method('getSkautis')->willReturnCallback(
            fn () => new Skautis($wsdlManager, new User($wsdlManager, new SessionAdapter())),
        );

        return new SkautisService(
            $skautisFactory,
            $this->participantRepository,
            self::createStub(UserService::class),
            self::createStub(UserRegeneration::class),
            Mockery::mock(UserRepository::class),
            $this->flashMessages,
            $this->logger,
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    private function storedLoginData(): array
    {
        $adapterData = $_SESSION['__Skautis\SessionAdapter\SessionAdapter'] ?? null;
        self::assertIsArray($adapterData);
        $loginData = $adapterData['skautis_user_data'];
        self::assertIsArray($loginData);

        return $loginData;
    }
}
