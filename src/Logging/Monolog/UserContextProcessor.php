<?php

declare(strict_types=1);

namespace kissj\Logging\Monolog;

use kissj\User\User;
use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

readonly class UserContextProcessor implements ProcessorInterface
{
    public function __construct(
        private ?User $user,
    ) {
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $user = $this->user;

        $record = $record->with(context: [
            'user' => [
                'authenticated' => $user instanceof User,
                'id' => $user?->id,
                'email' => $user?->email,
            ],
        ]);

        return $record;
    }
}
