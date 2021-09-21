<?php

namespace kissj\Orm;


class Relation {
    public function __construct(public $value, public $relation = '=')
    {
    }
}
