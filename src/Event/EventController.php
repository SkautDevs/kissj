<?php

namespace kissj\Event;

class EventController {/*
    public function createEvent(Request $request, Response $response, array $args) {
        $params = $request->getParams();
        /** @var \kissj\Event\EventService $eventService *//*
        $eventService = $this->eventService;
        if ($eventService->isEventDetailsValid(
            $params['slug'] ?? null,
            $params['readableName'] ?? null,
            $params['accountNumber'] ?? null,
            $params['automaticPaymentPairing'] ?? null,
            $params['prefixVariableSymbol'] ?? null,
            $params['bankId'] ?? null,
            $params['bankApi'] ?? null,
            $params['allowPatrols'] ?? null,
            $params['maximalClosedPatrolsCount'] ?? null,
            $params['minimalPatrolParticipantsCount'] ?? null,
            $params['maximalPatrolParticipantsCount'] ?? null,
            $params['allowIsts'] ?? null,
            $params['maximalClosedIstsCount'] ?? null)) {

            /** @var \kissj\Event\Event $newEvent *//*
            $newEvent = $eventService->createEvent(
                $params['slug'] ?? null,
                $params['readableName'] ?? null,
                $params['accountNumber'] ?? null,
                $params['prefixVariableSymbol'] ?? null,
                $params['automaticPaymentPairing'] ?? null,
                $params['bankId'] ?? null,
                $params['bankApi'] ?? null,
                $params['allowPatrols'] ?? null,
                $params['maximalClosedPatrolsCount'] ?? null,
                $params['minimalPatrolParticipantsCount'] ?? null,
                $params['maximalPatrolParticipantsCount'] ?? null,
                $params['allowIsts'] ?? null,
                $params['maximalClosedIstsCount'] ?? null);

            $this->flashMessages->success('Registrace je úspěšně vytvořená!');
            $this->logger->info('Created event with ID '.$newEvent->id.' and slug '.$newEvent->slug);

            return $response->withRedirect($this->router->urlFor('getDashboard',
                ['eventSlug' => $newEvent->slug]));
        }

        $this->flashMessages->warning('Některé údaje nebyly validní - prosím zkus zadání údajů znovu.');

        return $response->withRedirect($this->router->urlFor('createEvent'));
        // TODO add event-admins (roles table?)
    }

    public function getDashboard(Request $request, Response $response, array $args) {
        $roleName = $this->roleService->getRole($request->getAttribute('user'))->name;
        if (!$this->roleService->isUserRoleNameValid($roleName)) {
            throw new \RuntimeException('Unknown role "'.$roleName.'"');
        }

        switch ($roleName) {
            case 'patrol-leader':
            {
                return $response->withRedirect($this->router->urlFor('pl-dashboard'));
            }
            case 'ist':
            {
                return $response->withRedirect($this->router->urlFor('ist-dashboard'));
            }
            case 'admin':
            {
                return $response->withRedirect($this->router->urlFor('admin-dashboard'));
            }
            default:
            {
                throw new \RuntimeException('Non-implemented role "'.$roleName.'"!');
            }
        }

    }*/
}
