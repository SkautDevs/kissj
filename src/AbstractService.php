<?php

namespace kissj;

use kissj\Event\AbstractContentArbiter;
use kissj\Participant\Participant;

class AbstractService
{
    /**
     * @param string[]    $params
     * @param Participant $p
     * @return Participant
     * @throws \Exception
     */
    public function addParamsIntoPerson(array $params, Participant $p): Participant
    {
        // TODO move into participant service
        $p->contingent = $params['contingent'] ?? null;
        $p->firstName = $params['firstName'] ?? null;
        $p->lastName = $params['lastName'] ?? null;
        $p->nickname = $params['nickname'] ?? null;
        if (array_key_exists('birthDate', $params) && $params['birthDate'] !== null) {
            $p->birthDate = new \DateTime($params['birthDate']);
        }
        $p->gender = $params['gender'] ?? null;
        $p->email = $params['email'] ?? null;
        $p->telephoneNumber = $params['telephoneNumber'] ?? null;
        $p->permanentResidence = $params['permanentResidence'] ?? null;
        $p->country = $params['country'] ?? null;
        $p->scoutUnit = $params['scoutUnit'] ?? null;
        $p->setTshirt($params['tshirtShape'] ?? null, $params['tshirtSize'] ?? null);
        $p->foodPreferences = $params['foodPreferences'] ?? null;
        $p->healthProblems = $params['healthProblems'] ?? null;
        $p->idNumber = $params['idNumber'] ?? null;
        $p->languages = $params['languages'] ?? null;
        $p->swimming = $params['swimming'] ?? null;
        $p->scarf = $params['scarf'] ?? null;
        if (array_key_exists('arrivalDate', $params) && $params['arrivalDate'] !== null) {
            $p->arrivalDate = new \DateTime($params['arrivalDate']);
        }
        if (array_key_exists('departueDate', $params) && $params['departueDate'] !== null) {
            $p->departureDate = new \DateTime($params['departueDate']);
        }
        $p->notes = $params['notes'] ?? null;

        return $p;
    }

    // TODO move into ParticipantService
    public function isPersonValidForClose(Participant $p, AbstractContentArbiter $ca): bool
    {
        if (
            ($ca->contingent && $p->contingent === null)
            || ($ca->firstName && $p->firstName === null)
            || ($ca->lastName && $p->lastName === null)
            || ($ca->birthDate && $p->birthDate === null)
            || ($ca->gender && $p->gender === null)
            || ($ca->email && $p->email === null)
            || ($ca->phone && $p->telephoneNumber === null)
            || ($ca->address && $p->permanentResidence === null)
            || ($ca->country && $p->country === null)
            || ($ca->unit && $p->scoutUnit === null)
            || ($ca->food && $p->foodPreferences === null)
            || ($ca->languages && $p->languages === null)
            || ($ca->swimming && $p->swimming === null)
            || ($ca->idNumber && $p->idNumber === null)
            || ($ca->uploadFile && $p->uploadedFilename === null)
            || ($ca->tshirt && $p->getTshirtShape() === null)
            || ($ca->tshirt && $p->getTshirtSize() === null)
        ) {
            return false;
        }

        if ($ca->email && !empty($p->email) && filter_var($p->email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        // numbers and plus sight up front only
        if ($ca->phone && !empty ($p->telephoneNumber) && preg_match('/^\+?\d+$/', $p->telephoneNumber) === 0) {
            return false;
        }

        return true;
    }
}
