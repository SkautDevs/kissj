<?php declare(strict_types=1);

namespace kissj\Event;

class ContentArbiterTroopLeader extends AbstractContentArbiter
{
    public function __construct()
    {
        parent::__construct();
        $this->patrolName->allowed = true;
    }
}
