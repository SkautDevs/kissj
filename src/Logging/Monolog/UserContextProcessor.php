<?php declare(strict_types=1);

namespace kissj\Logging\Monolog;

use kissj\User\User;
use Monolog\Processor\ProcessorInterface;

final class UserContextProcessor implements ProcessorInterface
{
    public function __construct(
        private ?User $user,
    ) {}

    /**
     * @param array<mixed> $record
     * @return array<mixed>
     */
    public function __invoke(array $record): array
    {
        $user = $this->user;

        $record['context']['user'] = [
            'authenticated' => ($user instanceof User),
            'id' => $user?->id,
            'email' => $user?->email,
        ];

        return $record;
    }
}
