<?php
declare(strict_types=1);

namespace kissj\User;

use Symfony\Component\Form\FormTypeInterface;

readonly class InputEmail
{
    public function __construct(
        public string $email,
    ) {
    }
}
