<?php

declare(strict_types=1);

namespace kissj\Programme\Place;

final readonly class Coordinates
{
    public function __construct(
        public float $lat,
        public float $lng,
    ) {
    }

    public static function tryParse(string $input): ?self
    {
        $parts = preg_split('/[\s,;]+/', trim($input));
        if (
            $parts === false
            || count($parts) !== 2
            || is_numeric($parts[0]) === false
            || is_numeric($parts[1]) === false
        ) {
            return null;
        }

        $lat = round((float)$parts[0], 6);
        $lng = round((float)$parts[1], 6);
        if ($lat < -90.0 || $lat > 90.0 || $lng < -180.0 || $lng > 180.0) {
            return null;
        }

        return new self($lat, $lng);
    }

    public function format(): string
    {
        return sprintf('%.6f, %.6f', $this->lat, $this->lng);
    }
}
