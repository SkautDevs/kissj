<?php

declare(strict_types=1);

namespace kissj\Event;

class ContentArbiterTechnicalTeamParticipant extends AbstractContentArbiter
{
    public function __construct()
    {
        parent::__construct();
        $this->gender->allowed = false;
        $this->birthDate->allowed = false;
        $this->address->allowed = false;
        $this->health->allowed = false;
        $this->psychicalHealth->allowed = false;
        $this->notes->allowed = false;

        $this->food->allowed = true;
        $this->scarf->allowed = true;
        $this->scarf->required = false;
        $this->scarf->options = [
            '' => 'detail.foodChoose',
            'yes' => 'detail.scarfTechnicalTeamYes',
            'no' => 'detail.scarfNo',
        ];
        $this->arrivalDate->allowed = true;
        $this->departureDate->allowed = true;
    }
}
