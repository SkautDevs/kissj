<?php

declare(strict_types=1);

namespace kissj\Entry;

use Symfony\Contracts\Translation\TranslatorInterface;

class EntryParticipant
{
    // registration form stores translation keys, e.g. "detail.tshirtGenderMale-detail.tshirtXL"
    private const string RAW_SHAPE_PREFIX = 'detail.tshirtGender';
    private const string RAW_SIZE_PREFIX = 'detail.tshirt';

    /** @var EntryParticipant[] */
    public array $participants = [];

    public function __construct(
        public readonly int $id,
        public readonly string $firstname,
        public readonly string $lastname,
        public readonly string $nickname,
        public readonly ?string $patrolName,
        public readonly string $tieCode,
        public readonly \DateTimeInterface $birthDate,
        public readonly EntryStatus $entryStatus,
        public readonly bool $sfh,
        public readonly ?string $tshirtShape = null,
        public readonly ?string $tshirtSize = null,
    ) {
    }

    public static function tshirtShapeFromRaw(?string $raw, TranslatorInterface $translator): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        $translated = $translator->trans($raw);
        if ($translated !== $raw) {
            return $translated;
        }
        if (str_starts_with($raw, self::RAW_SHAPE_PREFIX)) {
            return strtolower(substr($raw, strlen(self::RAW_SHAPE_PREFIX)));
        }

        return $raw;
    }

    public static function tshirtSizeFromRaw(?string $raw, TranslatorInterface $translator): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        $translated = $translator->trans($raw);
        if ($translated !== $raw) {
            return $translated;
        }
        if (str_starts_with($raw, self::RAW_SIZE_PREFIX)) {
            return substr($raw, strlen(self::RAW_SIZE_PREFIX));
        }

        return $raw;
    }
}
