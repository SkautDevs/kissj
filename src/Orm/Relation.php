<?php
/**
 * Created by PhpStorm.
 * User: peci1
 * Date: 6.12.17
 * Time: 21:31
 */

namespace kissj\Orm;


class Relation
{
    public function __construct($value, $relation = "=") {
        $this->value = $value;
        $this->relation = $relation;
    }
}