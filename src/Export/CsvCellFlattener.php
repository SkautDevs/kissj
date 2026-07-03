<?php

declare(strict_types=1);

namespace kissj\Export;

use LogicException;

readonly class CsvCellFlattener
{
    /**
     * @param array<int|string, mixed> $row
     * @return array<int|string, string>
     */
    public function __invoke(array $row): array
    {
        return array_map(
            static function (mixed $cell): string {
                if ($cell !== null && !is_scalar($cell)) {
                    throw new LogicException('CSV cell must be scalar or null');
                }

                $withoutBreaks = preg_replace('/\R+/u', ' ', (string)$cell) ?? '';

                return trim($withoutBreaks);
            },
            $row,
        );
    }
}
