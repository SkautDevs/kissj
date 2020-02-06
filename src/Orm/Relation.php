<?php

namespace kissj\Orm;


class Relation {
    public function __construct($value, $relation = "=") {
        $this->value = $value;
        $this->relation = $relation;
    }
}
