<?php

declare(strict_types=1);

namespace kissj\Participant;

use kissj\Entry\EntryParticipant;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class TshirtService
{
    // registration form stores translation keys, e.g. "detail.tshirtGenderMale-detail.tshirtXL"
    private const string RAW_SHAPE_PREFIX = 'detail.tshirtGender';
    private const string RAW_SIZE_PREFIX = 'detail.tshirt';

    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function displayShape(?string $raw): ?string
    {
        return $this->display($raw, self::RAW_SHAPE_PREFIX, lowercase: true);
    }

    public function displaySize(?string $raw): ?string
    {
        return $this->display($raw, self::RAW_SIZE_PREFIX, lowercase: false);
    }

    public function translateEntryParticipantTree(EntryParticipant $participant): EntryParticipant
    {
        $translated = new EntryParticipant(
            $participant->id,
            $participant->firstname,
            $participant->lastname,
            $participant->nickname,
            $participant->patrolName,
            $participant->tieCode,
            $participant->birthDate,
            $participant->entryStatus,
            $participant->sfh,
            $this->displayShape($participant->tshirtShape),
            $this->displaySize($participant->tshirtSize),
        );

        foreach ($participant->participants as $id => $child) {
            $translated->participants[$id] = $this->translateEntryParticipantTree($child);
        }

        return $translated;
    }

    private function display(?string $raw, string $prefix, bool $lowercase): ?string
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $translated = $this->translator->trans($raw);
        if ($translated !== $raw) {
            return $translated;
        }

        if (str_starts_with($raw, $prefix)) {
            $stripped = substr($raw, strlen($prefix));

            return $lowercase ? strtolower($stripped) : $stripped;
        }

        return $raw;
    }
}
