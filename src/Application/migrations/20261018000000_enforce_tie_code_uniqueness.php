<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class EnforceTieCodeUniqueness extends AbstractMigration
{
    // copied on purpose - this migration must not follow later changes to the app's generator
    private const string TIE_CODE_ALPHABET = 'ABCDEFGHJKLMNOPQRSTUVWXYZ';
    private const int TIE_CODE_LENGTH = 6;
    private const string LEGACY_DEFAULT_CODE = '123456';
    private const string TROOP_LEADER_ROLE = 'tl';

    public function up(): void
    {
        // live pods keep registering during the deploy hook - block inserts until the index exists
        if ($this->getAdapter()?->getAdapterType() === 'pgsql') {
            $this->execute('LOCK TABLE participant IN SHARE ROW EXCLUSIVE MODE');
        }

        $this->rewriteLegacyAndDuplicateCodes();

        $this->table('participant')
            ->addIndex(['tie_code'], ['unique' => true, 'name' => 'participant_tie_code_uindex'])
            ->save();
    }

    // rewritten codes are not restored - the repair is one-way
    public function down(): void
    {
        $this->table('participant')
            ->removeIndexByName('participant_tie_code_uindex')
            ->save();
    }

    private function rewriteLegacyAndDuplicateCodes(): void
    {
        $participants = $this->fetchParticipants();
        $usedCodes = array_fill_keys(array_column($participants, 'tieCode'), true);

        $groups = [];
        foreach ($participants as $participant) {
            $groups[$participant['tieCode']][] = $participant;
        }

        $legacyCount = 0;
        $duplicateCount = 0;
        $troopLeaderCount = 0;

        foreach ($groups as $group) {
            $keptId = $group[0]['tieCode'] === self::LEGACY_DEFAULT_CODE ? null : $this->pickKeptId($group);

            foreach ($group as $participant) {
                if ($participant['id'] === $keptId) {
                    continue;
                }

                $newCode = $this->generateUnusedTieCode($usedCodes);
                $usedCodes[$newCode] = true;
                $this->execute('UPDATE participant SET tie_code = ? WHERE id = ?', [$newCode, $participant['id']]);

                if ($keptId === null) {
                    $legacyCount++;
                    $reason = 'legacy default';
                } else {
                    $duplicateCount++;
                    $reason = sprintf('duplicate of participant %d', $keptId);
                }

                $this->getOutput()?->writeln(sprintf(
                    'rewrote tie code of participant %d (%s) %s -> %s: %s',
                    $participant['id'],
                    $participant['role'] ?? '',
                    $participant['tieCode'],
                    $newCode,
                    $reason,
                ));

                if ($participant['role'] === self::TROOP_LEADER_ROLE) {
                    $troopLeaderCount++;
                }
            }
        }

        $this->getOutput()?->writeln(sprintf(
            'rewrote %d legacy and %d duplicate tie codes (%d troop leaders)',
            $legacyCount,
            $duplicateCount,
            $troopLeaderCount,
        ));
    }

    /**
     * @return list<array{id: int, role: ?string, tieCode: string}>
     */
    private function fetchParticipants(): array
    {
        $participants = [];
        foreach ($this->fetchAll('SELECT id, role, tie_code FROM participant ORDER BY id') as $row) {
            if (!is_array($row)) {
                throw new LogicException('Unexpected participant row shape');
            }
            $id = $row['id'] ?? null;
            $role = $row['role'] ?? null;
            $tieCode = $row['tie_code'] ?? null;
            if ((!is_int($id) && !is_string($id)) || ($role !== null && !is_string($role)) || !is_string($tieCode)) {
                throw new LogicException('Unexpected participant row types');
            }

            $participants[] = ['id' => (int)$id, 'role' => $role, 'tieCode' => $tieCode];
        }

        return $participants;
    }

    /**
     * The troop leader with the highest id keeps the code, or the highest id of the group when it has none.
     *
     * @param non-empty-list<array{id: int, role: ?string, tieCode: string}> $group ordered by id ascending
     */
    private function pickKeptId(array $group): int
    {
        $newestId = $group[0]['id'];
        $newestTroopLeaderId = null;
        foreach ($group as $participant) {
            $newestId = $participant['id'];
            if ($participant['role'] === self::TROOP_LEADER_ROLE) {
                $newestTroopLeaderId = $participant['id'];
            }
        }

        return $newestTroopLeaderId ?? $newestId;
    }

    /**
     * @param array<string, true> $usedCodes
     */
    private function generateUnusedTieCode(array $usedCodes): string
    {
        do {
            $code = substr(
                str_shuffle(str_repeat(self::TIE_CODE_ALPHABET, self::TIE_CODE_LENGTH * 9)),
                0,
                self::TIE_CODE_LENGTH,
            );
        } while (isset($usedCodes[$code]));

        return $code;
    }
}
