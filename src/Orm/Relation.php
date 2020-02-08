<?php

namespace kissj\Orm;


class Relation {
    public $value;
    public $relation;

    public function __construct($value, $relation = '=') {
        $this->value = $value;
        $this->relation = $relation;
    }
}
