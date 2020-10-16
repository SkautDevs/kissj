<?php

namespace kissj;

use kissj\Event\AbstractContentArbiter;
use kissj\Participant\Participant;
use Slim\Psr7\UploadedFile;

class AbstractService {
    public function addParamsIntoPerson(array $params, Participant $p): Participant {
        $p->firstName = $params['firstName'] ?? null;
        $p->lastName = $params['lastName'] ?? null;
        $p->nickname = $params['nickname'] ?? null;
        if ($params['birthDate'] !== null) {
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
        $p->languages = $params['languages'] ?? null;
        $p->swimming = $params['swimming'] ?? null;
        $p->scarf = $params['scarf'] ?? null;
        $p->notes = $params['notes'] ?? null;

        return $p;
    }

    public function isPersonValidForClose(Participant $p, AbstractContentArbiter $ca): bool {
        if (
            ($ca->firstName && $p->firstName === null)
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

    public function saveFileTo(Participant $participant, UploadedFile $uploadedFile): Participant {
        $uploadDirectory = __DIR__.'/../../../uploads/';
        $newFilename = \md5(microtime(true));
        $uploadedFile->moveTo($uploadDirectory.DIRECTORY_SEPARATOR.$newFilename); // TODO check if separator is needed

        $participant->uploadedFilename = $newFilename;
        $participant->uploadedOriginalFilename = $uploadedFile->getClientFilename();
        $participant->uploadedContenttype = $uploadedFile->getClientMediaType();

        return $participant;
    }
}
