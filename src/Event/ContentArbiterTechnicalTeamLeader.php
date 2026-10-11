<?php

declare(strict_types=1);

namespace kissj\Event;

class ContentArbiterTechnicalTeamLeader extends ContentArbiterTechnicalTeamParticipant
{
    public function __construct()
    {
        parent::__construct();
        $this->patrolName->allowed = true;
        $this->patrolName->label = 'detail.technicalTeamName';
        $this->email->allowed = true;
        $this->phone->allowed = true;
    }
}
