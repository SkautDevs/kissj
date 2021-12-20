<?php declare(strict_types=1);

namespace kissj\Orm;

class Relation
{
    public function __construct(
        public string $value,
        public string $relation = '=',
    ) {
    }
}
