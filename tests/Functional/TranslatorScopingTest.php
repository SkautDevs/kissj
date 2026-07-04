<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Translation\CurrentTranslator;
use LeanMapper\Connection;
use Tests\AppTestCase;

class TranslatorScopingTest extends AppTestCase
{
    public function testEventTypeOverridesApplyAndRevertBetweenRequests(): void
    {
        $app = $this->getTestApp();
        $connection = $this->getService($app, Connection::class);
        $connection->query(
            'UPDATE event SET event_type = %s WHERE slug = %s',
            'wsj',
            'test-event-slug',
        );

        $translator = $this->getService($app, CurrentTranslator::class);
        $key = 'chooseRole.registerAsIst';
        $baseValue = 'Registrovat se do servis týmu!';
        $wsjValue = 'Registrovat se do IST!';

        $app->handle($this->createRequest('/v2/event/test-event-slug/login'));
        self::assertSame($wsjValue, $translator->trans($key));

        $app->handle($this->createRequest('/'));
        self::assertSame($baseValue, $translator->trans($key));
    }
}
