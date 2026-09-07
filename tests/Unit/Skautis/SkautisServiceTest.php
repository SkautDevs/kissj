<?php

declare(strict_types=1);

namespace Tests\Unit\Skautis;

use kissj\FlashMessages\FlashMessagesInterface;
use kissj\Participant\ParticipantRepository;
use kissj\Skautis\SkautisFactory;
use kissj\Skautis\SkautisService;
use kissj\Telemetry\Sentry\Collector;
use kissj\User\UserRegeneration;
use kissj\User\UserRepository;
use kissj\User\UserService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SkautisServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private LoggerInterface&MockInterface $logger;
    private SkautisService $service;

    protected function setUp(): void
    {
        // the library's SessionAdapter reads the superglobal directly
        $_SESSION = [];
        $this->logger = Mockery::mock(LoggerInterface::class);
        $this->service = new SkautisService(
            new SkautisFactory(true),
            Mockery::mock(ParticipantRepository::class),
            self::createStub(UserService::class),
            self::createStub(UserRegeneration::class),
            Mockery::mock(UserRepository::class),
            Mockery::mock(FlashMessagesInterface::class),
            $this->logger,
            self::createStub(Collector::class),
        );
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
                Mockery::on(fn (array $context) => is_string($context['reason'] ?? null) && str_contains($context['reason'], '2026-09-08T00:00:00')),
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
