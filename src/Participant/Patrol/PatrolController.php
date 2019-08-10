<?php

namespace kissj\Participant\Patrol;

class PatrolController {
    public function getLeaderDashboard(Request $request, Response $response, array $args) {
        $user = $request->getAttribute('user');
        $patrolLeader = $this->patrolService->getPatrolLeader($user);
        $allParticipants = $this->patrolService->getAllParticipantsBelongsPatrolLeader($patrolLeader);
        $onePayment = $this->patrolService->getOnePayment($patrolLeader);

        return $this->view->render($response, 'dashboard-pl.twig', [
            'user' => $user,
            'plDetails' => $patrolLeader,
            'allPDetails' => $allParticipants,
            'payment' => $onePayment,
        ]);
    }

    public function showDetailsLeaderChangeable(Request $request, Response $response, array $args) {
        $plDetails = $this->patrolService->getPatrolLeader($request->getAttribute('user'));

        return $this->view->render($response, 'changeDetails-pl.twig', ['plInfo' => $plDetails]);
    }

    public function changeDetailsLeader(Request $request, Response $response, array $args) {
        $params = $request->getParams();
        if ($this->patrolService->isPatrolLeaderDetailsValid(
            $params['firstName'] ?? null,
            $params['lastName'] ?? null,
            $params['allergies'] ?? null,
            $params['birthDate'] ?? null,
            $params['birthPlace'] ?? null,
            $params['country'] ?? null,
            $params['gender'] ?? null,
            $params['permanentResidence'] ?? null,
            $params['scoutUnit'] ?? null,
            $params['telephoneNumber'] ?? null,
            $params['email'] ?? null,
            $params['foodPreferences'] ?? null,
            $params['cardPassportNumber'] ?? null,
            $params['notes'] ?? null,
            $params['patrolName'] ?? null)) {

            $patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
            $this->patrolService->editPatrolLeaderInfo(
                $patrolLeader,
                $params['firstName'] ?? null,
                $params['lastName'] ?? null,
                $params['allergies'] ?? null,
                $params['birthDate'] ?? null,
                $params['birthPlace'] ?? null,
                $params['country'] ?? null,
                $params['gender'] ?? null,
                $params['permanentResidence'] ?? null,
                $params['scoutUnit'] ?? null,
                $params['telephoneNumber'] ?? null,
                $params['email'] ?? null,
                $params['foodPreferences'] ?? null,
                $params['cardPassportNumber'] ?? null,
                $params['notes'] ?? null,
                $params['patrolName'] ?? null);

            $this->flashMessages->success('Údaje úspěšně uloženy');

            return $response->withRedirect($this->router->pathFor('pl-dashboard'));
        }

        $this->flashMessages->warning('Některé údaje nebyly validní - prosím zkus úpravu údajů znovu.');

        return $response->withRedirect($this->router->pathFor('pl-changeDetails'));
    }

    public function showCloseRegistration(Request $request, Response $response, array $args) {
        $patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
        $validRegistration = $this->patrolService->isCloseRegistrationValid($patrolLeader); // call because of warnings
        if ($validRegistration) {
            return $this->view->render($response, 'closeRegistration-pl.twig');
        }

        return $response->withRedirect($this->router->pathFor('pl-dashboard'));
    }

    public function closeRegistration(Request $request, Response $response, array $args) {
        $patrolLeader = $this->patrolService->getPatrolLeader($request->getAttribute('user'));
        if ($this->patrolService->isCloseRegistrationValid($patrolLeader)) {
            $this->patrolService->closeRegistration($patrolLeader);
            $this->flashMessages->success('Registrace úspěšně uzavřena, čeká na schválení');
            $this->logger->info('Closing registration for PatrolLeader with ID '.$patrolLeader->id);

            return $response->withRedirect($this->router->pathFor('pl-dashboard'));
        }

        $this->flashMessages->error('Registraci ještě nelze uzavřít');

        return $response->withRedirect($this->router->pathFor('pl-dashboard'));
    }

    public function addParticipant(Request $request, Response $response, array $args) {
        // create participant and reroute to edit him
        $newParticipant = $this->patrolService->addPatrolParticipant($this->patrolService->getPatrolLeader($request->getAttribute('user')));

        return $response->withRedirect($this->router->pathFor('p-changeDetails',
            ['participantId' => $newParticipant->id]));
    }

    public function showChangeDetailsParticipant(Request $request, Response $response, array $args) {
        $pDetails = $this->patrolService->getPatrolParticipant($args['participantId']);

        return $this->view->render($response, 'changeDetails-p.twig',
            ['pDetails' => $pDetails]);
    }

    public function cangeDetailsParticipant(Request $request, Response $response, array $args) {
        $params = $request->getParams();

        if ($this->patrolService->isPatrolParticipantDetailsValid(
            $params['firstName'] ?? null,
            $params['lastName'] ?? null,
            $params['allergies'] ?? null,
            $params['birthDate'] ?? null,
            $params['birthPlace'] ?? null,
            $params['country'] ?? null,
            $params['gender'] ?? null,
            $params['permanentResidence'] ?? null,
            $params['scoutUnit'] ?? null,
            $params['telephoneNumber'] ?? null,
            $params['email'] ?? null,
            $params['foodPreferences'] ?? null,
            $params['cardPassportNumber'] ?? null,
            $params['notes'] ?? null,
            $params['patrolName'] ?? null)) {

            $this->patrolService->editPatrolParticipant(
                $this->patrolService->getPatrolParticipant($args['participantId']),
                $params['firstName'] ?? null,
                $params['lastName'] ?? null,
                $params['allergies'] ?? null,
                $params['birthDate'] ?? null,
                $params['birthPlace'] ?? null,
                $params['country'] ?? null,
                $params['gender'] ?? null,
                $params['permanentResidence'] ?? null,
                $params['scoutUnit'] ?? null,
                $params['telephoneNumber'] ?? null,
                $params['email'] ?? null,
                $params['foodPreferences'] ?? null,
                $params['cardPassportNumber'] ?? null,
                $params['notes'] ?? null);

            $this->flashMessages->success('Účastník úspěšně uložen');

            return $response->withRedirect($this->router->pathFor('pl-dashboard'));
        }

        $this->flashMessages->warning('Některé údaje nebyly validní - prosím zkus přidat účastníka znovu.');

        return $response->withRedirect($this->router->pathFor('pl-addParticipant'));
    }

    public function showDeleteParticipant(Request $request, Response $response, array $args) {
        $pDetails = $this->patrolService->getPatrolParticipant($args['participantId']);

        return $this->view->render($response, 'delete-p.twig', ['pDetail' => $pDetails]);
    }

    public function deleteParticipant(Request $request, Response $response, array $args) {
        $patrolParticipant = $this->patrolService->getPatrolParticipant($args['participantId']);
        $this->patrolService->deletePatrolParticipant($patrolParticipant);
        $this->flashMessages->success('Účastník úspěšně vymazán!');

        return $response->withRedirect($this->router->pathFor('pl-dashboard'));
    }
}
