<?php

declare(strict_types=1);

namespace kissj\Orm;

class Order
{
    public const string DIRECTION_ASC = 'ASC';
    public const string DIRECTION_DESC = 'DESC';

    public const string COLUMN_UPDATED_AT = 'updated_at';

    public function __construct(
        private readonly string $field,
        private readonly string $order = self::DIRECTION_ASC,
    ) {
    }

    public function getField(): string
    {
        return $this->field;
    }

    public function isOrderAsc(): bool
    {
        return $this->order === self::DIRECTION_ASC;
    }
}
