<?php

declare(strict_types=1);

namespace Tests\Unit;

use kissj\AbstractController;
use kissj\FlashMessages\FlashMessagesBySession;
use kissj\Participant\RegistrationCloseResult;
use Mockery;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class FlashRegistrationCloseResultTest extends TestCase
{
    public function testTranslatesListParamsAndPassesStringParamsThrough(): void
    {
        $translator = Mockery::mock(TranslatorInterface::class);
        $translator->shouldReceive('trans')->with('detail.surname')->andReturn('Surname');
        $translator->shouldReceive('trans')->with('detail.phone')->andReturn('Phone number');

        /** @var list<array{key: string, params: array<string, string>}> $flashed */
        $flashed = [];
        $flashMessages = Mockery::mock(FlashMessagesBySession::class);
        $flashMessages->shouldReceive('warning')->andReturnUsing(
            function (string $key, array $params) use (&$flashed): void {
                $flashed[] = ['key' => $key, 'params' => $params];
            }
        );

        $controller = new class () extends AbstractController {
            public function configure(TranslatorInterface $translator, FlashMessagesBySession $flashMessages): void
            {
                $this->translator = $translator;
                $this->flashMessages = $flashMessages;
            }

            public function flash(RegistrationCloseResult $result): void
            {
                $this->flashRegistrationCloseResult($result);
            }
        };
        $controller->configure($translator, $flashMessages);

        $result = RegistrationCloseResult::startChecking()
            ->withWarning('flash.warning.noLockFields', ['%fields%' => ['detail.surname', 'detail.phone']])
            ->withWarning('flash.warning.plTooFewParticipants', ['%minimalTroopParticipantsCount%' => '3']);

        $controller->flash($result);

        self::assertCount(2, $flashed);
        // list<string> label keys are translated element-wise and comma-joined
        self::assertSame('Surname, Phone number', $flashed[0]['params']['%fields%']);
        // plain string params pass through untranslated
        self::assertSame('3', $flashed[1]['params']['%minimalTroopParticipantsCount%']);
    }
}
