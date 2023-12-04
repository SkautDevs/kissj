<?php

declare(strict_types=1);

namespace kissj\Event\ContentArbiter;

class ContentArbiterItem
{
    /**
     * @param array<string> $extraClasses
     */
    public function __construct(
        public string $id,
        public bool $allowed,
        public ContentArbiterItemType $type,
        public int $order,
        public string $label,
        public string $placeholder,
        public array $extraClasses,
        public bool $required,
        public ?string $defaultValue = null,
        public ?string $pattern = null,
    ) {
    }
}
