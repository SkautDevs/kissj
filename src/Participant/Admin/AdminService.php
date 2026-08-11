<?php

declare(strict_types=1);

namespace kissj\Participant\Admin;

use kissj\Event\AbstractContentArbiter;
use kissj\Event\Event;
use kissj\Event\EventType\EventType;
use kissj\Orm\Order;
use kissj\Participant\Participant;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantRole;
use kissj\User\User;
use kissj\User\UserStatus;
use LogicException;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class AdminService
{
    public const array IMPORT_HEADER = ['firstName', 'lastName', 'subcamp', 'internalUniqueId', 'internalCommonId'];

    private const string NAME_KEY_SEPARATOR = '|';

    public function __construct(
        private ParticipantRepository $participantRepository,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param array<string, string|null> $roleEmptyTitleKeys role value => empty-state translation key
     * @return list<ParticipantListSection>
     */
    public function getParticipantSections(
        Event $event,
        User $user,
        UserStatus $status,
        array $roleEmptyTitleKeys,
        bool $orderByUpdatedAt = false,
        bool $filterEmpty = false,
    ): array {
        $eventType = $event->getEventType();
        $orders = $orderByUpdatedAt
            ? [new Order(Order::COLUMN_UPDATED_AT, Order::DIRECTION_DESC)]
            : [];

        $sections = [];
        foreach ($roleEmptyTitleKeys as $roleValue => $emptyTitleKey) {
            $role = ParticipantRole::from($roleValue);
            if (!$event->isRoleEnabled($role)) {
                continue;
            }

            $sections[] = new ParticipantListSection(
                $role,
                'role.' . $role->value,
                $this->participantRepository->getAllParticipantsWithStatus(
                    [$role],
                    [$status],
                    $event,
                    $user,
                    $orders,
                    $filterEmpty,
                ),
                $eventType->getContentArbiterForRole($role),
                $this->getChildContentArbiter($eventType, $role),
                $emptyTitleKey,
            );
        }

        return $sections;
    }

    private function getChildContentArbiter(EventType $eventType, ParticipantRole $role): ?AbstractContentArbiter
    {
        return match ($role) {
            ParticipantRole::PatrolLeader => $eventType->getContentArbiterForRole(ParticipantRole::PatrolParticipant),
            ParticipantRole::TroopLeader => $eventType->getContentArbiterForRole(ParticipantRole::TroopParticipant),
            ParticipantRole::PatrolParticipant,
            ParticipantRole::TroopParticipant,
            ParticipantRole::Ist,
            ParticipantRole::Guest,
            ParticipantRole::OrganizingTeam => null,
        };
    }

    /**
     * Always overwrites all three organizer-assigned values - a null (or empty) input clears the stored value.
     */
    public function setAdminValues(
        Participant $participant,
        Event $event,
        ?string $subcamp,
        ?string $internalUniqueId,
        ?string $internalCommonId,
    ): AdminValuesSaveResult {
        // HTML forms post empty strings for blank inputs, but "not filled in" must stay null
        $subcamp = $subcamp === '' ? null : $subcamp;
        $internalUniqueId = $internalUniqueId === '' ? null : $internalUniqueId;
        $internalCommonId = $internalCommonId === '' ? null : $internalCommonId;

        if ($subcamp !== null && !in_array($subcamp, $event->getEventType()->getSubcamps(), true)) {
            return AdminValuesSaveResult::UnknownSubcamp;
        }

        if (
            $internalUniqueId !== null
            && $this->isInternalUniqueIdTakenByOther($event, $internalUniqueId, $participant)
        ) {
            return AdminValuesSaveResult::UniqueIdTaken;
        }

        $this->writeAdminValues($participant, $subcamp, $internalUniqueId, $internalCommonId);

        return AdminValuesSaveResult::Saved;
    }

    private function writeAdminValues(
        Participant $participant,
        ?string $subcamp,
        ?string $internalUniqueId,
        ?string $internalCommonId,
    ): void {
        $participant->subcamp = $subcamp;
        $participant->internalUniqueId = $internalUniqueId;
        $participant->internalCommonId = $internalCommonId;
        $this->participantRepository->persist($participant);
    }

    private function isInternalUniqueIdTakenByOther(
        Event $event,
        string $internalUniqueId,
        Participant $participant,
    ): bool {
        foreach ($this->participantRepository->getEventParticipantsWithInternalUniqueId($event) as $other) {
            if ($other->id !== $participant->id && $other->internalUniqueId === $internalUniqueId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, int> internal unique ID => ID of the participant holding it
     */
    private function getInternalUniqueIdOwnerIds(Event $event): array
    {
        $ownerIds = [];
        foreach ($this->participantRepository->getEventParticipantsWithInternalUniqueId($event) as $holder) {
            if ($holder->internalUniqueId !== null) {
                $ownerIds[$holder->internalUniqueId] = $holder->id;
            }
        }

        return $ownerIds;
    }

    /**
     * Matches pasted CSV rows against the event participants and either only reports (dry run) or writes the values.
     * Rows with a problem are always skipped, valid rows around them are still applied.
     */
    public function importAdminValues(Event $event, string $csv, bool $apply): AdminValuesImportReport
    {
        $lines = preg_split('/\R/', trim($csv));
        if ($lines === false) {
            throw new LogicException('splitting the imported CSV into lines failed');
        }

        if ($this->parseCsvCells($lines[0]) !== self::IMPORT_HEADER) {
            return new AdminValuesImportReport(
                0,
                [new AdminValuesImportProblem(1, $lines[0], AdminValuesImportProblemType::InvalidHeader)],
            );
        }

        $subcampKeysByName = $this->getSubcampKeysByDisplayName($event);
        $participants = $this->participantRepository->getEventParticipantsForAdminValues($event);

        /** @var array<string, list<Participant>> $participantsByName */
        $participantsByName = [];
        foreach ($participants as $participant) {
            $firstName = (string)$participant->firstName;
            $lastName = (string)$participant->lastName;
            if (trim($firstName . $lastName) !== '') {
                $participantsByName[$this->normalizeName($firstName, $lastName)][] = $participant;
            }
        }

        $uniqueIdOwnerIds = $this->getInternalUniqueIdOwnerIds($event);

        /** @var list<AdminValuesImportProblem> $problems */
        $problems = [];
        /** @var list<array{Participant, ?string, ?string, ?string}> $validRows */
        $validRows = [];
        /** @var array<string, true> $usedNameKeys */
        $usedNameKeys = [];
        /** @var array<string, true> $usedUniqueIds */
        $usedUniqueIds = [];

        foreach (array_slice($lines, 1) as $index => $line) {
            $lineNumber = $index + 2;
            if (trim($line) === '') {
                continue;
            }

            $cells = $this->parseCsvCells($line);
            if (count($cells) !== count(self::IMPORT_HEADER)) {
                $problems[] = new AdminValuesImportProblem(
                    $lineNumber,
                    $line,
                    AdminValuesImportProblemType::InvalidRow,
                );
                continue;
            }

            [$firstName, $lastName, $subcampName, $internalUniqueId, $internalCommonId] = $cells;
            $name = trim($firstName . ' ' . $lastName);
            $nameKey = $this->normalizeName($firstName, $lastName);

            if (array_key_exists($nameKey, $usedNameKeys)) {
                $problems[] = new AdminValuesImportProblem(
                    $lineNumber,
                    $name,
                    AdminValuesImportProblemType::DuplicateNameInCsv,
                );
                continue;
            }

            $matchedParticipants = $participantsByName[$nameKey] ?? [];
            if ($matchedParticipants === []) {
                $problems[] = new AdminValuesImportProblem($lineNumber, $name, AdminValuesImportProblemType::NoMatch);
                continue;
            }

            if (count($matchedParticipants) > 1) {
                $problems[] = new AdminValuesImportProblem(
                    $lineNumber,
                    $name,
                    AdminValuesImportProblemType::AmbiguousName,
                );
                continue;
            }

            $participant = $matchedParticipants[0];

            $subcamp = null;
            if ($subcampName !== '') {
                $subcamp = $subcampKeysByName[mb_strtolower($subcampName)] ?? null;
                if ($subcamp === null) {
                    $problems[] = new AdminValuesImportProblem(
                        $lineNumber,
                        $name,
                        AdminValuesImportProblemType::UnknownSubcamp,
                    );
                    continue;
                }
            }

            if ($internalUniqueId !== '') {
                if (array_key_exists($internalUniqueId, $usedUniqueIds)) {
                    $problems[] = new AdminValuesImportProblem(
                        $lineNumber,
                        $name,
                        AdminValuesImportProblemType::DuplicateUniqueIdInCsv,
                    );
                    continue;
                }

                $ownerId = $uniqueIdOwnerIds[$internalUniqueId] ?? null;
                if ($ownerId !== null && $ownerId !== $participant->id) {
                    $problems[] = new AdminValuesImportProblem(
                        $lineNumber,
                        $name,
                        AdminValuesImportProblemType::UniqueIdTakenByOther,
                    );
                    continue;
                }

                $usedUniqueIds[$internalUniqueId] = true;
            }

            $usedNameKeys[$nameKey] = true;
            $validRows[] = [
                $participant,
                $subcamp,
                $internalUniqueId === '' ? null : $internalUniqueId,
                $internalCommonId === '' ? null : $internalCommonId,
            ];
        }

        if ($apply) {
            foreach ($validRows as [$participant, $subcamp, $internalUniqueId, $internalCommonId]) {
                $this->writeAdminValues($participant, $subcamp, $internalUniqueId, $internalCommonId);
            }
        }

        return new AdminValuesImportReport(count($validRows), $problems);
    }

    /**
     * @return array<string, string> lowercased displayed subcamp name => subcamp translation key
     */
    private function getSubcampKeysByDisplayName(Event $event): array
    {
        // subcamp names are matched in the admin's current locale
        // - safe while CEJ, the only event with subcamps, is en-only
        $subcampKeysByName = [];
        foreach ($event->getEventType()->getSubcamps() as $subcampKey) {
            $subcampKeysByName[mb_strtolower(trim($this->translator->trans($subcampKey)))] = $subcampKey;
        }

        return $subcampKeysByName;
    }

    /**
     * @return list<string>
     */
    private function parseCsvCells(string $line): array
    {
        return array_map(
            static fn (?string $cell): string => trim($cell ?? ''),
            str_getcsv($line, ',', '"', '\\'),
        );
    }

    private function normalizeName(string $firstName, string $lastName): string
    {
        return mb_strtolower(trim($firstName)) . self::NAME_KEY_SEPARATOR . mb_strtolower(trim($lastName));
    }
}
