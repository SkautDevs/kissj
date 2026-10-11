<?php

declare(strict_types=1);

namespace kissj\Event;

class ContentArbiterTechnicalTeamLeader extends ContentArbiterTechnicalTeamParticipant
{
    public function __construct()
    {
        parent::__construct();
        $this->technicalTeamName->allowed = true;
        $this->email->allowed = true;
        $this->phone->allowed = true;
    }
}
