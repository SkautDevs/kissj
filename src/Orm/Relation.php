<?php

namespace kissj\Orm;


class Relation {
    private $value;
    private $relation;

    public function __construct($value, $relation = '=') {
        $this->value = $value;
        $this->relation = $relation;
    }
}
