<?php

declare(strict_types=1);

namespace kissj\Mailer;

class EmbedDTO
{
    /**
     * @param resource $resource
     */
    public function __construct(
        public string $name,
        public mixed $resource,
        public ?string $contentType = null,
    ) {
    }
}
