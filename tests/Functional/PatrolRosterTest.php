<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Event\EventRepository;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Patrol\PatrolsRoster;
use kissj\Participant\Patrol\SinglePatrolRoster;
use kissj\PdfGenerator\PdfGenerator;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\AppTestCase;

class PatrolRosterTest extends AppTestCase
{
    private const string PATROL_NAME = 'roster tshirt test patrol';

    public function testGetPatrolsRosterCarriesTranslatedTshirtSizes(): void
    {
        $app = $this->getTestApp();
        $userService = $this->getService($app, UserService::class);
        $userRepository = $this->getService($app, UserRepository::class);
        $eventRepository = $this->getService($app, EventRepository::class);
        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $translator = $this->getService($app, TranslatorInterface::class);

        $event = $this->getSmallTestEvent($eventRepository);

        $user = $userService->registerEmailUser('roster-tshirt-test@example.com', $event);
        $participant = $userService->createParticipantSetRole($user, 'pl');
        $patrolLeader = $patrolLeaderRepository->get($participant->id);
        $patrolLeader->patrolName = self::PATROL_NAME;
        $patrolLeader->firstName = 'Roster';
        $patrolLeader->lastName = 'Leader';
        $patrolLeader->setTshirt('detail.tshirtGenderMale', 'detail.tshirtXL');
        $patrolLeaderRepository->persist($patrolLeader);

        $participantWithShirt = new PatrolParticipant();
        $participantWithShirt->patrolLeader = $patrolLeader;
        $participantWithShirt->firstName = 'With';
        $participantWithShirt->lastName = 'Shirt';
        $participantWithShirt->setTshirt('detail.tshirtGenderFemale', 'detail.tshirtM');
        $patrolParticipantRepository->persist($participantWithShirt);

        $participantWithoutShirt = new PatrolParticipant();
        $participantWithoutShirt->patrolLeader = $patrolLeader;
        $participantWithoutShirt->firstName = 'Without';
        $participantWithoutShirt->lastName = 'Shirt';
        $patrolParticipantRepository->persist($participantWithoutShirt);

        $user->status = UserStatus::Paid;
        $userRepository->persist($user);

        $roster = $participantRepository->getPatrolsRoster($event, $translator);

        $ourPatrol = null;
        foreach ($roster->patrolsRoster as $singlePatrolRoster) {
            if ($singlePatrolRoster->patrolName === self::PATROL_NAME) {
                $ourPatrol = $singlePatrolRoster;
            }
        }

        self::assertInstanceOf(SinglePatrolRoster::class, $ourPatrol);
        self::assertSame('XL (větší)', $ourPatrol->patrolLeaderTshirtSize);

        $sizesByName = [];
        foreach ($ourPatrol->patrolParticipants as $rosterParticipant) {
            $sizesByName[$rosterParticipant['name']] = $rosterParticipant['tshirtSize'];
        }

        self::assertSame('M (střední)', $sizesByName['With Shirt']);
        self::assertNull($sizesByName['Without Shirt']);
    }

    public function testGeneratePatrolRosterPdfRendersWithTshirtColumn(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $pdfGenerator = $this->getService($app, PdfGenerator::class);

        $event = $this->getSmallTestEvent($eventRepository);
        $roster = new PatrolsRoster([
            new SinglePatrolRoster('1', 'Roster pdf patrol', '', 'Roster Leader', 'XL (větší)', [
                ['name' => 'With Shirt', 'tshirtSize' => 'M (střední)'],
                ['name' => 'Without Shirt', 'tshirtSize' => null],
            ]),
            new SinglePatrolRoster('2', 'Roster pdf patrol two', '', 'Second Leader', null, []),
        ]);

        $pdfBytes = $pdfGenerator->generatePatrolRoster(
            $event,
            $roster,
            $event->eventType->getRosterTemplateName(),
        );

        self::assertStringStartsWith('%PDF', $pdfBytes);
    }
}
