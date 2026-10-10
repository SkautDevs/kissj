<?php

declare(strict_types=1);

namespace kissj\Event;

class ContentArbiterOrganizingTeam extends AbstractContentArbiter
{
    public function __construct()
    {
        parent::__construct();
        $this->phone->allowed = true;
        $this->email->allowed = true;
    }
}
