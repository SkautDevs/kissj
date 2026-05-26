<?php declare(strict_types=1);

namespace Tests\Functional;

use kissj\Translation\CurrentTranslator;
use LeanMapper\Connection;
use Psr\Container\ContainerInterface;
use Tests\AppTestCase;

class TranslatorScopingTest extends AppTestCase
{
    public function testEventTypeOverridesApplyAndRevertBetweenRequests(): void
    {
        $app = $this->getTestApp();
        /** @var ContainerInterface $container */
        $container = $app->getContainer();
        /** @var Connection $connection */
        $connection = $container->get(Connection::class);
        $connection->query(
            'UPDATE event SET event_type = %s WHERE slug = %s',
            'wsj',
            'test-event-slug',
        );

        /** @var CurrentTranslator $translator */
        $translator = $container->get(CurrentTranslator::class);
        $key = 'chooseRole.registerAsIst';
        $baseValue = 'Registrovat se do servis týmu!';
        $wsjValue = 'Registrovat se do IST!';

        $app->handle($this->createRequest('/v2/event/test-event-slug/login'));
        self::assertSame($wsjValue, $translator->trans($key));

        $app->handle($this->createRequest('/'));
        self::assertSame($baseValue, $translator->trans($key));
    }
}
