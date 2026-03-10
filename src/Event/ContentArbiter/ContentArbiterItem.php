<?php

declare(strict_types=1);

namespace kissj\Event\ContentArbiter;

class ContentArbiterItem
{
    /**
     * @param array<string, string> $options key=dbValue, value=translationKey, backward compatibility for selfMappedOptions
     * @param list<string> $extraClasses
     */
    public function __construct(
        public string $slug,
        public bool $allowed,
        public ContentArbiterItemType $type,
        public int $order,
        public string $label,
        public ?string $placeholder = null,
        public bool $required = true,
        public ?string $defaultValue = null,
        public ?string $pattern = null,
        public array $options = [],
        public array $extraClasses = [],
        public ?string $helpText = null,
        public ?AgeGroup $ageGroup = null,
    ) {
    }

    /**
     * @param list<string> $translationKeys
     * @return array<string, string>
     */
    public static function selfMappedOptions(array $translationKeys): array
    {
        return array_combine($translationKeys, $translationKeys);
    }
}
