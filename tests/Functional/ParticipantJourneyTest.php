<?php

declare(strict_types=1);

namespace Tests\Functional;

use kissj\Application\DateTimeUtils;
use kissj\Event\Event;
use kissj\Event\EventRepository;
use PHPUnit\Framework\Attributes\Group;
use kissj\Mailer\MailerSettings;
use kissj\Participant\Guest\Guest;
use kissj\Participant\Guest\GuestRepository;
use kissj\Participant\Ist\IstRepository;
use kissj\Participant\OrganizingTeam\OrganizingTeam;
use kissj\Participant\ParticipantRepository;
use kissj\Participant\ParticipantService;
use kissj\Participant\Patrol\PatrolLeader;
use kissj\Participant\Patrol\PatrolLeaderRepository;
use kissj\Participant\Patrol\PatrolParticipant;
use kissj\Participant\Patrol\PatrolParticipantRepository;
use kissj\Participant\Patrol\PatrolService;
use kissj\Participant\Troop\TroopLeader;
use kissj\Participant\Troop\TroopLeaderRepository;
use kissj\Participant\Troop\TroopParticipant;
use kissj\Participant\Troop\TroopParticipantRepository;
use kissj\Participant\Troop\TroopService;
use kissj\Payment\Payment;
use kissj\Payment\PaymentRepository;
use kissj\Payment\PaymentService;
use kissj\Payment\PaymentStatus;
use kissj\User\LoginToken;
use kissj\User\LoginTokenRepository;
use kissj\User\User;
use kissj\User\UserRepository;
use kissj\User\UserService;
use kissj\User\UserStatus;
use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Views\Twig;
use Tests\AppTestCase;

class ParticipantJourneyTest extends AppTestCase
{
    private const string TEST_EVENT_SLUG = 'test-event-slug';
    private const string BASE_URL = '/v2/event/' . self::TEST_EVENT_SLUG;

    /**
     * Test complete IST registration happy path:
     * Register → Login → Choose IST Role → Fill Details → Lock → Admin Approve → Pay
     */
    public function testIstFullRegistrationJourney(): void
    {
        $app = $this->getTestApp();

        $email = 'ist-test@example.com';

        // Step 1: Register and get login token
        $user = $this->registerUser($app, $email);
        $loginToken = $this->createLoginToken($app, $user);

        // Step 2: Login with token
        $responseLogin = $app->handle($this->createRequest(
            self::BASE_URL . '/tryLogin/' . $loginToken->token
        ));
        self::assertSame(302, $responseLogin->getStatusCode());

        // Step 3: User should be redirected to choose role
        // Simulate session by creating participant with IST role
        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        // Refresh user to get updated status
        $userRepository = $this->getService($app, UserRepository::class);
        $user = $userRepository->get($user->id);
        self::assertSame(UserStatus::Open, $user->status);

        // Step 4: Fill IST details
        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $ist->firstName = 'Test';
        $ist->lastName = 'IST';
        $ist->nickname = 'Tester';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);

        // Step 5: Lock registration (update user status)
        $user->status = UserStatus::Closed;
        $userRepository->persist($user);

        self::assertSame(UserStatus::Closed, $user->status);

        // Step 6: Admin approves
        $user->status = UserStatus::Approved;
        $userRepository->persist($user);

        // Step 7: Generate payment
        $paymentService = $this->getService($app, PaymentService::class);
        $ist = $istRepository->get($ist->id); // Refresh
        $payment = $paymentService->createAndPersistNewEventPayment($ist);

        self::assertSame(PaymentStatus::Waiting, $payment->status);

        // Step 8: Confirm payment
        $paymentService->confirmPayment($payment);

        $paymentRepository = $this->getService($app, PaymentRepository::class);
        $updatedPayment = $paymentRepository->get($payment->id);
        self::assertInstanceOf(Payment::class, $updatedPayment);

        self::assertSame(PaymentStatus::Paid, $updatedPayment->status);

        // Verify final user status
        $finalUser = $userRepository->get($user->id);
        self::assertSame(UserStatus::Paid, $finalUser->status);
    }

    /**
     * Test Patrol Leader with participants registration:
     * Register Leader → Add Participants → Fill Details → Lock → Approve → Pay
     */
    #[Group('patrol')]
    public function testPatrolLeaderWithParticipantsJourney(): void
    {
        $app = $this->getTestApp();

        // Ensure event has enough capacity for new registrations
        // Note: PostgreSQL accumulates data across test runs, so use high limits
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        self::assertNotNull($event);
        $event->maximalClosedPatrolsCount = 10000;  // High limit for accumulated test data
        // Set min/max participants per patrol (null defaults to 0 which fails validation)
        $event->minimalPatrolParticipantsCount = 1;
        $event->maximalPatrolParticipantsCount = 10;
        $eventRepository->persist($event);

        $leaderEmail = 'patrol-leader@example.com';

        // Step 1: Register patrol leader
        $leaderUser = $this->registerUser($app, $leaderEmail);

        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($leaderUser, 'pl');

        // Step 2: Fill ALL required leader details (based on AbstractContentArbiter defaults)
        $patrolLeaderRepository = $this->getService($app, PatrolLeaderRepository::class);
        /** @var PatrolLeader $patrolLeader */
        $patrolLeader = $patrolLeaderRepository->get($participant->id);

        $patrolLeader->patrolName = 'Test Patrol';
        $patrolLeader->firstName = 'Leader';
        $patrolLeader->lastName = 'Test';
        $patrolLeader->nickname = 'LeaderNick';
        $patrolLeader->permanentResidence = '123 Test Street, Test City';
        $patrolLeader->gender = 'female';
        $patrolLeader->birthDate = DateTimeUtils::getDateTime('1995-05-15');
        $patrolLeader->healthProblems = 'None';
        $patrolLeader->psychicalHealthProblems = 'None';
        $patrolLeader->notes = 'Test notes';
        $patrolLeader->email = $leaderEmail;
        $patrolLeader->contingent = 'detail.contingent.czechia';
        $patrolLeader->telephoneNumber = '+420111222333';
        $patrolLeader->country = 'detail.countryCzechRepublic';
        $patrolLeader->languages = 'Czech, English';
        $patrolLeader->birthPlace = 'Prague';
        $patrolLeader->emergencyContact = 'Emergency Contact, +420999888777';
        $patrolLeader->foodPreferences = 'detail.foodWithout';
        $patrolLeader->idNumber = 'AB123456';
        $patrolLeader->swimming = 'good';
        $patrolLeader->setTshirt('male', 'L');
        $patrolLeaderRepository->persist($patrolLeader);

        // Step 3: Add patrol participants with ALL required fields
        $patrolService = $this->getService($app, PatrolService::class);
        $patrolParticipantRepository = $this->getService($app, PatrolParticipantRepository::class);

        $participant1 = new PatrolParticipant();
        $participant1->patrolLeader = $patrolLeader;
        $participant1->firstName = 'Participant';
        $participant1->lastName = 'One';
        $participant1->nickname = 'P1';
        $participant1->permanentResidence = '456 Test Ave, Test Town';
        $participant1->gender = 'male';
        $participant1->birthDate = DateTimeUtils::getDateTime('2005-03-20');
        $participant1->healthProblems = 'None';
        $participant1->psychicalHealthProblems = 'None';
        $participant1->notes = '';
        $participant1->email = 'participant1@example.com';
        $participant1->telephoneNumber = '+420111222444';
        $participant1->country = 'detail.countryCzechRepublic';
        $participant1->languages = 'Czech';
        $participant1->birthPlace = 'Brno';
        $participant1->emergencyContact = 'Parent One, +420999888666';
        $participant1->foodPreferences = 'detail.foodWithout';
        $participant1->idNumber = 'CD123456';
        $participant1->swimming = 'good';
        $participant1->setTshirt('male', 'M');
        $patrolParticipantRepository->persist($participant1);

        $participant2 = new PatrolParticipant();
        $participant2->patrolLeader = $patrolLeader;
        $participant2->firstName = 'Participant';
        $participant2->lastName = 'Two';
        $participant2->nickname = 'P2';
        $participant2->permanentResidence = '789 Test Blvd, Test Village';
        $participant2->gender = 'female';
        $participant2->birthDate = DateTimeUtils::getDateTime('2006-07-10');
        $participant2->healthProblems = 'None';
        $participant2->psychicalHealthProblems = 'None';
        $participant2->notes = '';
        $participant2->email = 'participant2@example.com';
        $participant2->telephoneNumber = '+420111222555';
        $participant2->country = 'detail.countryCzechRepublic';
        $participant2->languages = 'Czech';
        $participant2->birthPlace = 'Olomouc';
        $participant2->emergencyContact = 'Parent Two, +420999888555';
        $participant2->foodPreferences = 'detail.foodVegetarian';
        $participant2->idNumber = 'EF123456';
        $participant2->swimming = 'average';
        $participant2->setTshirt('female', 'S');
        $patrolParticipantRepository->persist($participant2);

        // Verify participants are linked (need minimum 2 for test event)
        $patrolLeader = $patrolLeaderRepository->get($patrolLeader->id); // Refresh
        self::assertSame(2, $patrolLeader->getPatrolParticipantsCount());

        // Initialize mailer settings (normally done by middleware)
        $this->initializeMailerSettings($app, $leaderUser->event);

        // Step 4: Lock patrol registration
        $patrolService->closeRegistration($patrolLeader);

        $userRepository = $this->getService($app, UserRepository::class);
        $leaderUser = $userRepository->get($leaderUser->id);
        self::assertSame(UserStatus::Closed, $leaderUser->status);

        // Step 5: Admin approves
        $leaderUser->status = UserStatus::Approved;
        $userRepository->persist($leaderUser);

        // Step 6: Generate and confirm payment
        $paymentService = $this->getService($app, PaymentService::class);
        $patrolLeader = $patrolLeaderRepository->get($patrolLeader->id); // Refresh
        $payment = $paymentService->createAndPersistNewEventPayment($patrolLeader);
        $paymentService->confirmPayment($payment);

        // Verify final status
        $finalUser = $userRepository->get($leaderUser->id);
        self::assertSame(UserStatus::Paid, $finalUser->status);
    }

    /**
     * Test login flow via HTTP requests
     */
    public function testLoginViaHttpRequests(): void
    {
        $app = $this->getTestApp();
        $email = 'http-test@example.com';

        $responseLoginPage = $app->handle($this->createRequest(self::BASE_URL . '/login'));
        self::assertSame(200, $responseLoginPage->getStatusCode());
        self::assertStringContainsString('form-email', (string)$responseLoginPage->getBody());

        $app = $this->getTestApp(false);
        $responseSubmitEmail = $app->handle($this->createRequest(
            self::BASE_URL . '/login',
            'POST',
            ['email' => $email]
        ));
        self::assertSame(302, $responseSubmitEmail->getStatusCode());

        $app = $this->getTestApp(false);
        $linkSentUrl = $responseSubmitEmail->getHeaderLine('Location');
        $responseLinkSent = $app->handle($this->createRequest($linkSentUrl));
        self::assertSame(200, $responseLinkSent->getStatusCode());
    }

    /**
     * Test that IST dashboard renders without error even when no details are filled
     */
    public function testDashboardRendersForIstWithoutDetails(): void
    {
        $app = $this->getTestApp();
        $email = 'dashboard-empty-test@example.com';
        $user = $this->registerUser($app, $email);

        $userService = $this->getService($app, UserService::class);
        $userService->createParticipantSetRole($user, 'ist');

        $_SESSION['user'] = ['id' => $user->id];

        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/dashboard'
        ));

        self::assertSame(200, $response->getStatusCode());
    }

    /**
     * Test that IST dashboard renders successfully with filled details
     */
    public function testDashboardRendersForIstWithDetails(): void
    {
        $app = $this->getTestApp();
        $email = 'dashboard-render-test@example.com';
        $user = $this->registerUser($app, $email);

        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Test';
        $ist->lastName = 'IST';
        $ist->nickname = 'Tester';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $ist->permanentResidence = 'Test Street 1';
        $ist->telephoneNumber = '+420123456789';
        $ist->scoutUnit = 'Test Unit';
        $ist->foodPreferences = 'none';
        $ist->healthProblems = '';
        $ist->psychicalHealthProblems = '';
        $ist->swimming = 'detail.swimSkillMore50';
        $ist->driversLicense = 'dont';
        $ist->notes = '';
        $istRepository->persist($ist);

        $_SESSION['user'] = ['id' => $user->id];

        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/dashboard'
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('Test', $body);
        self::assertStringContainsString('IST', $body);
    }

    public function testDashboardShowsFullRegistrationFlashForOpenParticipantInFullRole(): void
    {
        $app = $this->getTestApp();

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        self::assertInstanceOf(Event::class, $event);

        // no closed ISTs yet, so a zero cap already means the role is full
        $event->maximalClosedIstsCount = 0;
        $eventRepository->persist($event);

        $email = 'dashboard-full-flash-test@example.com';
        $user = $this->registerUser($app, $email);

        $userService = $this->getService($app, UserService::class);
        $userService->createParticipantSetRole($user, 'ist');

        $_SESSION['user'] = ['id' => $user->id];

        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/dashboard'
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringContainsString('plno', $body);
    }

    public function testDashboardDoesNotShowFullRegistrationFlashForClosedParticipant(): void
    {
        $app = $this->getTestApp();

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        self::assertInstanceOf(Event::class, $event);

        $event->maximalClosedIstsCount = 0;
        $eventRepository->persist($event);

        $email = 'dashboard-full-flash-closed-test@example.com';
        $user = $this->registerUser($app, $email);

        $userService = $this->getService($app, UserService::class);
        $userService->createParticipantSetRole($user, 'ist');

        $user->status = UserStatus::Closed;
        $userRepository = $this->getService($app, UserRepository::class);
        $userRepository->persist($user);

        $_SESSION['user'] = ['id' => $user->id];

        $app = $this->getTestApp(false);

        $response = $app->handle($this->createRequest(
            self::BASE_URL . '/participant/dashboard'
        ));

        self::assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        self::assertStringNotContainsString('plno', $body);
    }

    /**
     * Test Guest registration journey:
     * Register → Choose Guest Role → Fill Details → Close → Admin Approve → Paid (no payment needed)
     *
     * Guests have price 0 by default, so approval sets them directly to Paid with no Payment created.
     */
    #[Group('guest')]
    public function testGuestRegistrationJourney(): void
    {
        $app = $this->getTestApp();

        $guestEmail = 'guest-test@example.com';

        // Step 1: Register guest user
        $guestUser = $this->registerUser($app, $guestEmail);
        self::assertSame(UserStatus::WithoutRole, $guestUser->status);

        // Ensure guestPrice is null (defaults to 0) for this test
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get($guestUser->event->id);
        $event->guestPrice = null;
        $eventRepository->persist($event);

        // Step 2: Choose guest role
        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($guestUser, 'guest');

        // Verify role is set and status is Open
        $userRepository = $this->getService($app, UserRepository::class);
        $guestUser = $userRepository->get($guestUser->id);
        self::assertSame(UserStatus::Open, $guestUser->status);

        // Step 3: Fill guest details
        $guestRepository = $this->getService($app, GuestRepository::class);
        /** @var Guest $guest */
        $guest = $guestRepository->get($participant->id);

        $guest->firstName = 'Test';
        $guest->lastName = 'Guest';
        $guest->nickname = 'Visitor';
        $guest->permanentResidence = '123 Guest Street, Guest City';
        $guest->gender = 'other';
        $guest->birthDate = DateTimeUtils::getDateTime('1985-08-20');
        $guest->telephoneNumber = '+420123456789';
        $guest->arrivalDate = DateTimeUtils::getDateTime('2026-07-01');
        $guest->departureDate = DateTimeUtils::getDateTime('2026-07-10');
        $guest->healthProblems = 'None';
        $guest->psychicalHealthProblems = 'None';
        $guest->notes = 'VIP guest';
        $guest->email = $guestEmail;
        $guestRepository->persist($guest);

        // Initialize mailer settings (normally done by middleware)
        $this->initializeMailerSettings($app, $guestUser->event);

        // Step 4: Close registration
        $participantService = $this->getService($app, ParticipantService::class);
        $participantService->closeRegistration($guest);

        // Step 5: Admin approves (Guest price is 0, so goes directly to Paid)
        $participantService->approveRegistration($guest);

        // Verify final status is Paid with no payments
        $finalUser = $userRepository->get($guestUser->id);
        self::assertSame(UserStatus::Paid, $finalUser->status);
        self::assertSame(0, $guest->countPaidPayments());
        self::assertSame(0, $guest->countWaitingPayments());
    }

    /**
     * Test Troop Leader with Troop Participants registration:
     * 1. Leader registers → chooses role → fills details
     * 2. Participants register independently → choose role → fill details
     * 3. Participants tie to leader using tie codes
     * 4. Participants close their registration
     * 5. Leader closes registration (must have minimum participants who are closed)
     * 6. Admin approves → Payment
     *
     * Key difference from Patrol: Troop participants register independently and use
     * tie codes to join a leader, rather than being added by the leader.
     */
    #[Group('troop')]
    public function testTroopLeaderWithParticipantsJourney(): void
    {
        $app = $this->getTestApp();
        // Enable troop registrations for test event (limits are null by default = full)
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        self::assertNotNull($event);
        $this->enableTroopForEvent($app, $event);

        $leaderEmail = 'troop-leader@example.com';
        $participant1Email = 'troop-participant1@example.com';
        $participant2Email = 'troop-participant2@example.com';

        // Step 1: Register and setup Troop Leader
        $leaderUser = $this->registerUser($app, $leaderEmail);

        $userService = $this->getService($app, UserService::class);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'tl');

        $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);
        /** @var TroopLeader $troopLeader */
        $troopLeader = $troopLeaderRepository->get($leaderParticipant->id);

        // Fill leader details (required fields from AbstractContentArbiter + patrolName for TroopLeader)
        $troopLeader->patrolName = 'Test Troop';  // Required for TroopLeader (reused as troop name)
        $troopLeader->firstName = 'Troop';
        $troopLeader->lastName = 'Leader';
        $troopLeader->nickname = 'Chief';
        $troopLeader->permanentResidence = '123 Leader Ave, Leader City';
        $troopLeader->gender = 'male';
        $troopLeader->birthDate = DateTimeUtils::getDateTime('1990-01-15');
        $troopLeader->healthProblems = 'None';
        $troopLeader->psychicalHealthProblems = 'None';
        $troopLeader->notes = 'Troop leader notes';
        $troopLeader->email = $leaderEmail;
        $troopLeaderRepository->persist($troopLeader);

        // Store the leader's tie code for verification
        $leaderTieCode = $troopLeader->tieCode;
        self::assertNotEmpty($leaderTieCode, 'Leader should have a tie code');

        // Step 2: Register Troop Participant 1
        $participant1User = $this->registerUser($app, $participant1Email);
        $participant1Data = $userService->createParticipantSetRole($participant1User, 'tp');

        $troopParticipantRepository = $this->getService($app, TroopParticipantRepository::class);
        /** @var TroopParticipant $troopParticipant1 */
        $troopParticipant1 = $troopParticipantRepository->get($participant1Data->id);

        // Fill participant 1 details
        $troopParticipant1->firstName = 'Scout';
        $troopParticipant1->lastName = 'One';
        $troopParticipant1->nickname = 'S1';
        $troopParticipant1->permanentResidence = '456 Scout Street, Scout Town';
        $troopParticipant1->gender = 'male';
        $troopParticipant1->birthDate = DateTimeUtils::getDateTime('2005-06-20');
        $troopParticipant1->healthProblems = 'None';
        $troopParticipant1->psychicalHealthProblems = 'None';
        $troopParticipant1->notes = '';
        $troopParticipant1->email = $participant1Email;
        $troopParticipantRepository->persist($troopParticipant1);

        $participant1TieCode = $troopParticipant1->tieCode;
        self::assertNotEmpty($participant1TieCode, 'Participant 1 should have a tie code');

        // Step 3: Register Troop Participant 2
        $participant2User = $this->registerUser($app, $participant2Email);
        $participant2Data = $userService->createParticipantSetRole($participant2User, 'tp');

        /** @var TroopParticipant $troopParticipant2 */
        $troopParticipant2 = $troopParticipantRepository->get($participant2Data->id);

        // Fill participant 2 details
        $troopParticipant2->firstName = 'Scout';
        $troopParticipant2->lastName = 'Two';
        $troopParticipant2->nickname = 'S2';
        $troopParticipant2->permanentResidence = '789 Scout Blvd, Scout Village';
        $troopParticipant2->gender = 'female';
        $troopParticipant2->birthDate = DateTimeUtils::getDateTime('2006-09-10');
        $troopParticipant2->healthProblems = 'None';
        $troopParticipant2->psychicalHealthProblems = 'None';
        $troopParticipant2->notes = '';
        $troopParticipant2->email = $participant2Email;
        $troopParticipantRepository->persist($troopParticipant2);

        $participant2TieCode = $troopParticipant2->tieCode;
        self::assertNotEmpty($participant2TieCode, 'Participant 2 should have a tie code');

        // Step 4: Tie participants to leader
        $troopService = $this->getService($app, TroopService::class);

        // Refresh leader to ensure status is current
        $userRepository = $this->getService($app, UserRepository::class);
        $leaderUser = $userRepository->get($leaderUser->id);
        self::assertSame(UserStatus::Open, $leaderUser->status, 'Leader must be in Open status to accept ties');

        // Tie participant 1 to leader
        $troopParticipant1 = $troopService->tieTroopParticipantToTroopLeader($troopParticipant1, $troopLeader);
        self::assertNotNull($troopParticipant1->troopLeader, 'Participant 1 should be tied to leader');
        self::assertSame($troopLeader->id, $troopParticipant1->troopLeader->id);

        // Tie participant 2 to leader
        $troopParticipant2 = $troopService->tieTroopParticipantToTroopLeader($troopParticipant2, $troopLeader);
        self::assertNotNull($troopParticipant2->troopLeader, 'Participant 2 should be tied to leader');
        self::assertSame($troopLeader->id, $troopParticipant2->troopLeader->id);

        // Verify leader has 2 participants
        $troopLeader = $troopLeaderRepository->get($troopLeader->id);
        self::assertSame(2, $troopLeader->getTroopParticipantsCount());

        // Step 5: Participants close their registration
        // Initialize mailer settings (normally done by middleware)
        $this->initializeMailerSettings($app, $leaderUser->event);

        $participantService = $this->getService($app, ParticipantService::class);

        // Participant 1 closes registration
        $troopParticipant1 = $troopParticipantRepository->get($troopParticipant1->id);
        $participantService->closeRegistration($troopParticipant1);
        $participant1User = $userRepository->get($participant1User->id);
        self::assertSame(UserStatus::Closed, $participant1User->status, 'Participant 1 should be Closed');

        // Participant 2 closes registration
        $troopParticipant2 = $troopParticipantRepository->get($troopParticipant2->id);
        $participantService->closeRegistration($troopParticipant2);
        $participant2User = $userRepository->get($participant2User->id);
        self::assertSame(UserStatus::Closed, $participant2User->status, 'Participant 2 should be Closed');

        // Step 6: Leader closes registration
        $troopLeader = $troopLeaderRepository->get($troopLeader->id);
        $participantService->closeRegistration($troopLeader);
        $leaderUser = $userRepository->get($leaderUser->id);
        self::assertSame(UserStatus::Closed, $leaderUser->status, 'Leader should be Closed');

        // Step 7: Admin approves leader
        $leaderUser->status = UserStatus::Approved;
        $userRepository->persist($leaderUser);

        // Step 8: Payment
        $paymentService = $this->getService($app, PaymentService::class);
        $troopLeader = $troopLeaderRepository->get($troopLeader->id);
        $payment = $paymentService->createAndPersistNewEventPayment($troopLeader);
        $paymentService->confirmPayment($payment);

        // Verify final status
        $finalLeaderUser = $userRepository->get($leaderUser->id);
        self::assertSame(UserStatus::Paid, $finalLeaderUser->status);
    }

    /**
     * Test tying troop participant to leader using tie codes (the typical user flow).
     * This tests the tryTieTogetherWithMessages method which uses tie codes.
     */
    #[Group('troop')]
    public function testTroopTieWithTieCodes(): void
    {
        $app = $this->getTestApp();

        // Enable troop registrations for test event
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        self::assertNotNull($event);
        $this->enableTroopForEvent($app, $event);

        // Create leader
        $leaderUser = $this->registerUser($app, 'tie-test-leader@example.com');
        $userService = $this->getService($app, UserService::class);
        $leaderParticipant = $userService->createParticipantSetRole($leaderUser, 'tl');

        $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);
        /** @var TroopLeader $troopLeader */
        $troopLeader = $troopLeaderRepository->get($leaderParticipant->id);
        $leaderTieCode = $troopLeader->tieCode;

        // Create participant
        $participantUser = $this->registerUser($app, 'tie-test-participant@example.com');
        $participantData = $userService->createParticipantSetRole($participantUser, 'tp');

        $troopParticipantRepository = $this->getService($app, TroopParticipantRepository::class);
        /** @var TroopParticipant $troopParticipant */
        $troopParticipant = $troopParticipantRepository->get($participantData->id);
        $participantTieCode = $troopParticipant->tieCode;

        // Verify not tied yet
        self::assertNull($troopParticipant->troopLeader, 'Should not be tied initially');

        // Tie using tie codes
        $troopService = $this->getService($app, TroopService::class);
        $result = $troopService->tryTieTogetherWithMessages(
            $leaderTieCode,
            $participantTieCode,
            $leaderUser->event
        );

        self::assertTrue($result, 'Tying should succeed');

        // Verify tied
        $troopParticipant = $troopParticipantRepository->get($troopParticipant->id);
        self::assertNotNull($troopParticipant->troopLeader, 'Should be tied after operation');
        self::assertSame($troopLeader->id, $troopParticipant->troopLeader->id);
    }

    #[Group('troop')]
    public function testSwapTroopLeaderWithParticipant(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        self::assertNotNull($event);
        $this->enableTroopForEvent($app, $event);

        $userRepository = $this->getService($app, UserRepository::class);
        $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);
        $troopService = $this->getService($app, TroopService::class);
        $paymentService = $this->getService($app, PaymentService::class);
        $paymentRepository = $this->getService($app, PaymentRepository::class);

        $troopLeader = $this->createTroopLeaderWithDetails($app, 'swap-leader@example.com', 'Swap Test Troop');
        $oldLeaderId = $troopLeader->id;

        $tp1 = $this->createTroopParticipantWithDetails($app, 'swap-tp1@example.com');
        $newLeaderId = $tp1->id;

        $tp2 = $this->createTroopParticipantWithDetails($app, 'swap-tp2@example.com');
        $tp2Id = $tp2->id;

        $troopService->tieTroopParticipantToTroopLeader($tp1, $troopLeader);
        $troopService->tieTroopParticipantToTroopLeader($tp2, $troopLeader);

        // Set all users to Approved so swap is allowed (same-status check)
        foreach ([$troopLeader, $tp1, $tp2] as $participant) {
            $user = $participant->getUserButNotNull();
            $user->status = UserStatus::Approved;
            $userRepository->persist($user);
        }

        // Create payments on both leader and participant to test bidirectional swap
        $troopLeader = $troopLeaderRepository->get($oldLeaderId);
        $leaderPayment = $paymentService->createAndPersistNewEventPayment($troopLeader);
        $leaderPaymentId = $leaderPayment->id;

        $troopParticipantRepository = $this->getService($app, TroopParticipantRepository::class);
        $tp1Refetched = $troopParticipantRepository->get($newLeaderId);
        /** @var TroopParticipant $tp1Refetched */
        $participantPayment = $paymentService->createAndPersistNewEventPayment($tp1Refetched);
        $participantPaymentId = $participantPayment->id;

        $troopService->swapTroopLeaderWithParticipant($tp1Refetched);

        $participantRepository = $this->getService($app, ParticipantRepository::class);

        // Verify: new leader has role 'tl' and troop name
        $newLeaderAfterSwap = $participantRepository->findParticipantById($newLeaderId, $event);
        self::assertNotNull($newLeaderAfterSwap);
        self::assertInstanceOf(TroopLeader::class, $newLeaderAfterSwap);
        self::assertSame('Swap Test Troop', $newLeaderAfterSwap->patrolName);

        // Verify: old leader has role 'tp' and is tied to new leader
        $oldLeaderAfterSwap = $participantRepository->findParticipantById($oldLeaderId, $event);
        self::assertNotNull($oldLeaderAfterSwap);
        self::assertInstanceOf(TroopParticipant::class, $oldLeaderAfterSwap);
        self::assertNotNull($oldLeaderAfterSwap->troopLeader);
        self::assertSame($newLeaderId, $oldLeaderAfterSwap->troopLeader->id);

        // Verify: tp2 is now tied to new leader
        $tp2AfterSwap = $participantRepository->findParticipantById($tp2Id, $event);
        self::assertNotNull($tp2AfterSwap);
        self::assertInstanceOf(TroopParticipant::class, $tp2AfterSwap);
        self::assertNotNull($tp2AfterSwap->troopLeader);
        self::assertSame($newLeaderId, $tp2AfterSwap->troopLeader->id);

        // Verify: new leader has 2 participants (old leader + tp2)
        /** @var TroopLeader $newLeaderEntity */
        $newLeaderEntity = $troopLeaderRepository->get($newLeaderId);
        self::assertSame(2, $newLeaderEntity->getTroopParticipantsCount());

        // Verify: leader's payment moved to new leader
        $paymentAfterSwap = $paymentRepository->getById($leaderPaymentId, $event);
        self::assertSame($newLeaderId, $paymentAfterSwap->participant->id);

        // Verify: participant's payment moved to demoted leader
        $participantPaymentAfterSwap = $paymentRepository->getById($participantPaymentId, $event);
        self::assertSame($oldLeaderId, $participantPaymentAfterSwap->participant->id);
    }

    #[Group('troop')]
    public function testSwapTroopLeaderRejectsStatusMismatch(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        self::assertNotNull($event);
        $this->enableTroopForEvent($app, $event);

        $userRepository = $this->getService($app, UserRepository::class);
        $troopService = $this->getService($app, TroopService::class);

        $troopLeader = $this->createTroopLeaderWithDetails($app, 'swap-mismatch-leader@example.com', 'Mismatch Troop');
        $tp = $this->createTroopParticipantWithDetails($app, 'swap-mismatch-tp@example.com');

        $troopService->tieTroopParticipantToTroopLeader($tp, $troopLeader);

        // Set different statuses
        $leaderUser = $troopLeader->getUserButNotNull();
        $leaderUser->status = UserStatus::Approved;
        $userRepository->persist($leaderUser);
        $tpUser = $tp->getUserButNotNull();
        $tpUser->status = UserStatus::Closed;
        $userRepository->persist($tpUser);

        $troopService->swapTroopLeaderWithParticipant($tp);

        // Verify roles unchanged
        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $leaderAfter = $participantRepository->findParticipantById($troopLeader->id, $event);
        self::assertInstanceOf(TroopLeader::class, $leaderAfter);
        $tpAfter = $participantRepository->findParticipantById($tp->id, $event);
        self::assertInstanceOf(TroopParticipant::class, $tpAfter);
    }

    #[Group('troop')]
    public function testSwapTroopLeaderRejectsNoTroopLeader(): void
    {
        $app = $this->getTestApp();
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        self::assertNotNull($event);
        $this->enableTroopForEvent($app, $event);

        $tp = $this->createTroopParticipantWithDetails($app, 'swap-no-leader@example.com');

        $troopService = $this->getService($app, TroopService::class);

        $this->expectException(\LogicException::class);
        $troopService->swapTroopLeaderWithParticipant($tp);
    }

    public function testSetRoleRejectsOtWithoutSessionFlag(): void
    {
        $app = $this->getTestApp();

        $email = 'ot-gate-test@example.com';
        $user = $this->registerUser($app, $email);
        $loginToken = $this->createLoginToken($app, $user);

        // Login via token (without ot_token param, so no session flag)
        $app->handle($this->createRequest(
            self::BASE_URL . '/tryLogin/' . $loginToken->token
        ));

        // Attempt to set OT role without session flag
        $app = $this->getTestApp(false);
        $responseSetRole = $app->handle($this->createRequest(
            self::BASE_URL . '/setRole',
            'POST',
            ['role' => 'ot'],
        ));

        // Should be redirected back to chooseRole (302), not to dashboard
        self::assertSame(302, $responseSetRole->getStatusCode());
        $location = $responseSetRole->getHeaderLine('Location');
        self::assertStringContainsString('chooseRole', $location);
    }

    /**
     * Test that approval with a nonzero price creates a Payment and keeps status at Approved.
     */
    #[Group('approval')]
    public function testApprovalWithPriceCreatesPayment(): void
    {
        $app = $this->getTestApp();

        $email = 'ist-price-test@example.com';
        $user = $this->registerUser($app, $email);

        // Set nonzero default price on the event
        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get($user->event->id);
        $event->defaultPrice = 100;
        $eventRepository->persist($event);

        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);

        $ist->firstName = 'Test';
        $ist->lastName = 'IST';
        $ist->nickname = 'Tester';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);

        $this->initializeMailerSettings($app, $user->event);

        $participantService = $this->getService($app, ParticipantService::class);
        $participantService->closeRegistration($ist);

        $participantService->approveRegistration($ist);

        $userRepository = $this->getService($app, UserRepository::class);
        $finalUser = $userRepository->get($user->id);
        self::assertSame(UserStatus::Approved, $finalUser->status);
        self::assertSame(1, $ist->countWaitingPayments());
    }

    /**
     * Test that OT with price=0 goes directly to Paid after approval, with no Payment created.
     */
    #[Group('approval')]
    public function testOrganizingTeamWithZeroPriceGoesToPaid(): void
    {
        $app = $this->getTestApp();

        $email = 'ot-zero-price@example.com';
        $user = $this->registerUser($app, $email);

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get($user->event->id);
        $event->allowOrganizingTeam = true;
        $event->organizingTeamPrice = 0;
        $event->organizingTeamRegistrationToken = 'test-token';
        $eventRepository->persist($event);

        $_SESSION['ot_access_granted'] = true;

        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ot');

        $participantRepository = $this->getService($app, ParticipantRepository::class);
        $ot = $participantRepository->get($participant->id);
        self::assertInstanceOf(OrganizingTeam::class, $ot);
        $ot->firstName = 'Test';
        $ot->lastName = 'OT';
        $ot->nickname = 'Organizer';
        $ot->permanentResidence = '123 OT Street, OT City';
        $ot->gender = 'male';
        $ot->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ot->healthProblems = 'None';
        $ot->psychicalHealthProblems = 'None';
        $ot->notes = '';
        $ot->email = $email;
        $participantRepository->persist($ot);

        $this->initializeMailerSettings($app, $event);

        $participantService = $this->getService($app, ParticipantService::class);
        $participantService->closeRegistration($ot);
        $participantService->approveRegistration($ot);

        $userRepository = $this->getService($app, UserRepository::class);
        $finalUser = $userRepository->get($user->id);
        self::assertSame(UserStatus::Paid, $finalUser->status);
        self::assertSame(0, $ot->countWaitingPayments());
    }

    /**
     * Test that Guest with nonzero guestPrice creates a Payment and stays at Approved.
     */
    #[Group('guest')]
    #[Group('approval')]
    public function testGuestWithNonzeroPriceCreatesPayment(): void
    {
        $app = $this->getTestApp();
        $email = 'guest-price-test@example.com';
        $user = $this->registerUser($app, $email);

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get($user->event->id);
        $event->guestPrice = 500;
        $eventRepository->persist($event);

        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'guest');

        $guestRepository = $this->getService($app, GuestRepository::class);
        /** @var Guest $guest */
        $guest = $guestRepository->get($participant->id);

        $guest->firstName = 'Paying';
        $guest->lastName = 'Guest';
        $guest->nickname = 'PG';
        $guest->permanentResidence = '123 Pay Street';
        $guest->gender = 'male';
        $guest->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $guest->healthProblems = 'None';
        $guest->psychicalHealthProblems = 'None';
        $guest->notes = '';
        $guest->email = $email;
        $guestRepository->persist($guest);

        $this->initializeMailerSettings($app, $event);

        $participantService = $this->getService($app, ParticipantService::class);
        $participantService->closeRegistration($guest);
        $participantService->approveRegistration($guest);

        $userRepository = $this->getService($app, UserRepository::class);
        $finalUser = $userRepository->get($user->id);
        self::assertSame(UserStatus::Approved, $finalUser->status);
        self::assertSame(1, $guest->countWaitingPayments());
    }

    /**
     * Test that changing payment price cancels old payment, creates new one,
     * and if price is 0, auto-confirms.
     */
    #[Group('payment')]
    public function testChangePaymentPrice(): void
    {
        $app = $this->getTestApp();

        $email = 'change-price-test@example.com';
        $user = $this->registerUser($app, $email);

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get($user->event->id);
        $event->defaultPrice = 500;
        $eventRepository->persist($event);

        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Price';
        $ist->lastName = 'Change';
        $ist->nickname = 'PC';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);

        $this->initializeMailerSettings($app, $event);

        $participantService = $this->getService($app, ParticipantService::class);
        $participantService->closeRegistration($ist);
        $participantService->approveRegistration($ist);

        // Verify initial payment
        $ist = $istRepository->get($ist->id);
        self::assertSame(1, $ist->countWaitingPayments());
        $payments = $ist->getNoncanceledPayments();
        self::assertCount(1, $payments);
        $originalPayment = $payments[0];

        // Change price to 300
        $newPayment = $participantService->changePaymentPrice($originalPayment, 300, 'Discount for early registration');

        // Refresh participant
        $ist = $istRepository->get($ist->id);

        // Old payment should be cancelled, new one waiting
        $paymentRepository = $this->getService($app, PaymentRepository::class);
        $oldPayment = $paymentRepository->get($originalPayment->id);
        self::assertInstanceOf(Payment::class, $oldPayment);
        self::assertSame(PaymentStatus::Canceled, $oldPayment->status);
        self::assertSame(PaymentStatus::Waiting, $newPayment->status);
        self::assertSame('300', $newPayment->price);
        self::assertSame(1, $ist->countWaitingPayments());
    }

    /**
     * Test that changing payment price to 0 auto-confirms and sets participant to Paid.
     */
    #[Group('payment')]
    public function testChangePaymentPriceToZeroAutoConfirms(): void
    {
        $app = $this->getTestApp();

        $email = 'zero-price-test@example.com';
        $user = $this->registerUser($app, $email);

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get($user->event->id);
        $event->defaultPrice = 500;
        $eventRepository->persist($event);

        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Zero';
        $ist->lastName = 'Price';
        $ist->nickname = 'ZP';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);

        $this->initializeMailerSettings($app, $event);

        $participantService = $this->getService($app, ParticipantService::class);
        $participantService->closeRegistration($ist);
        $participantService->approveRegistration($ist);

        $ist = $istRepository->get($ist->id);
        $payments = $ist->getNoncanceledPayments();
        $originalPayment = $payments[0];

        // Change price to 0 — should auto-confirm
        $newPayment = $participantService->changePaymentPrice($originalPayment, 0, 'Full scholarship');

        self::assertSame(PaymentStatus::Paid, $newPayment->status);

        $userRepository = $this->getService($app, UserRepository::class);
        $finalUser = $userRepository->get($user->id);
        self::assertSame(UserStatus::Paid, $finalUser->status);
    }

    /**
     * Test that changing price of a non-waiting payment throws RuntimeException.
     */
    #[Group('payment')]
    public function testChangePaymentPriceRejectsNonWaitingPayment(): void
    {
        $app = $this->getTestApp();

        $email = 'reject-price-test@example.com';
        $user = $this->registerUser($app, $email);

        $eventRepository = $this->getService($app, EventRepository::class);
        $event = $eventRepository->get($user->event->id);
        $event->defaultPrice = 500;
        $eventRepository->persist($event);

        $userService = $this->getService($app, UserService::class);
        $participant = $userService->createParticipantSetRole($user, 'ist');

        $istRepository = $this->getService($app, IstRepository::class);
        $ist = $istRepository->get($participant->id);
        $ist->firstName = 'Reject';
        $ist->lastName = 'Test';
        $ist->nickname = 'RT';
        $ist->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $ist->email = $email;
        $ist->gender = 'male';
        $ist->country = 'CZ';
        $ist->contingent = 'detail.contingent.czechia';
        $istRepository->persist($ist);

        $this->initializeMailerSettings($app, $event);

        $participantService = $this->getService($app, ParticipantService::class);
        $participantService->closeRegistration($ist);
        $participantService->approveRegistration($ist);

        $ist = $istRepository->get($ist->id);
        $payments = $ist->getNoncanceledPayments();
        $originalPayment = $payments[0];

        $paymentService = $this->getService($app, PaymentService::class);
        $paymentService->confirmPayment($originalPayment);

        $paymentRepository = $this->getService($app, PaymentRepository::class);
        $paidPayment = $paymentRepository->get($originalPayment->id);
        self::assertInstanceOf(Payment::class, $paidPayment);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot change price of payment that is not in waiting status');
        $participantService->changePaymentPrice($paidPayment, 300, 'Should fail');
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function registerUser(App $app, string $email): User
    {
        $userService = $this->getService($app, UserService::class);
        $eventRepository = $this->getService($app, EventRepository::class);

        $event = $eventRepository->findBySlug(self::TEST_EVENT_SLUG);
        if ($event === null) {
            // Fallback to first event if test-event-slug doesn't exist
            $event = $eventRepository->get(1);
        }

        return $userService->registerEmailUser($email, $event);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createLoginToken(App $app, User $user): LoginToken
    {
        $loginTokenRepository = $this->getService($app, LoginTokenRepository::class);

        $loginToken = new LoginToken();
        $loginToken->token = bin2hex(random_bytes(16));
        $loginToken->user = $user;
        $loginToken->used = false;
        $loginTokenRepository->persist($loginToken);

        return $loginToken;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function initializeMailerSettings(App $app, Event $event): void
    {
        $mailerSettings = $this->getService($app, MailerSettings::class);
        $mailerSettings->setEvent($event);
        $mailerSettings->setFullUrlLink('http://test.example.com/v2/event/' . $event->slug);

        $view = $this->getService($app, Twig::class);
        $view->getEnvironment()->addGlobal('event', $event);
    }

    /**
     * Configure event to allow troop registrations.
     * The test event doesn't have troop limits set by default (null = 0 = full),
     * so we need to set them for troop tests to work.
     *
     * @param App<ContainerInterface> $app
     */
    private function enableTroopForEvent(App $app, Event $event): void
    {
        $eventRepository = $this->getService($app, EventRepository::class);

        $event->allowTroops = true;
        // Use high limits to handle accumulated test data in PostgreSQL
        $event->maximalClosedTroopLeadersCount = 10000;
        $event->maximalClosedTroopParticipantsCount = 10000;
        // Also need to set min/max patrol participants count - used for validation of group sizes
        // These are used by getMinimalPpCount/getMaximalPpCount for TroopLeader validation
        $event->minimalPatrolParticipantsCount = 1;
        $event->maximalPatrolParticipantsCount = 10;
        $eventRepository->persist($event);
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createTroopLeaderWithDetails(
        App $app,
        string $email,
        string $troopName,
    ): TroopLeader {
        $userService = $this->getService($app, UserService::class);
        $troopLeaderRepository = $this->getService($app, TroopLeaderRepository::class);

        $user = $this->registerUser($app, $email);
        $participant = $userService->createParticipantSetRole($user, 'tl');
        /** @var TroopLeader $troopLeader */
        $troopLeader = $troopLeaderRepository->get($participant->id);
        $troopLeader->patrolName = $troopName;
        $troopLeader->firstName = 'Test';
        $troopLeader->lastName = 'Leader';
        $troopLeader->email = $email;
        $troopLeader->gender = 'male';
        $troopLeader->birthDate = DateTimeUtils::getDateTime('1990-01-01');
        $troopLeader->permanentResidence = 'Addr';
        $troopLeader->healthProblems = 'None';
        $troopLeader->psychicalHealthProblems = 'None';
        $troopLeader->notes = '';
        $troopLeaderRepository->persist($troopLeader);

        return $troopLeader;
    }

    /**
     * @param App<ContainerInterface> $app
     */
    private function createTroopParticipantWithDetails(
        App $app,
        string $email,
    ): TroopParticipant {
        $userService = $this->getService($app, UserService::class);
        $troopParticipantRepository = $this->getService($app, TroopParticipantRepository::class);

        $user = $this->registerUser($app, $email);
        $participant = $userService->createParticipantSetRole($user, 'tp');
        /** @var TroopParticipant $tp */
        $tp = $troopParticipantRepository->get($participant->id);
        $tp->firstName = 'Test';
        $tp->lastName = 'Participant';
        $tp->email = $email;
        $tp->gender = 'female';
        $tp->birthDate = DateTimeUtils::getDateTime('1995-01-01');
        $tp->permanentResidence = 'Addr';
        $tp->healthProblems = 'None';
        $tp->psychicalHealthProblems = 'None';
        $tp->notes = '';
        $troopParticipantRepository->persist($tp);

        return $tp;
    }
}
